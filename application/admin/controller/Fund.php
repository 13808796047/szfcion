<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\facade\Env;
use app\index\controller\Configure;

class Fund extends Base
{

    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

    /**
     * 基金首页
     */
    public function index(Request $req)
    {
        // 配置内容
        $config = Configure::get('hello.fund');
        $config = empty($config) ? [] : $config;
        $this->assign('config', $config);
        // 查询对象
        $query = Db::table('fund_holder');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', '=', $username);
        }
        // 查询数据
        $users = $query->order('money DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('users', $users);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 计算金额
     */
    private function compute($average, $official)
    {
        // 判断变量
        if (is_null($average) || $average < 0) {
            throw new \think\Exception("很抱歉、人均支付数额不正确！");
        }
        if ($official < 0) {
            throw new \think\Exception("很抱歉、官方补齐数额不正确！");
        }
        if ($average == 0 && $official == 0) {
            throw new \think\Exception("很抱歉、人均支付及官方补齐不能都为0！");
        }
        // 官方金额
        $official_money = 0;
        if ($official > 0) {
            $official_money = Db::table('fund_holder')->where('username', '=', '00000000000')->value('money');
            if (empty($official_money) || $official_money < $official) {
                throw new \think\Exception("很抱歉、官方金额不足！");
            }
        }
        // 统计人数
        $people = 0;
        if ($average > 0) {
            $people = Db::table('fund_holder')->where('money', '>=', $average)->where('username', '<>', '00000000000')->count('id');
            if (empty($people)) {
                throw new \think\Exception("很抱歉、人数不足！");
            }
        }
        // 总计金额
        $money = $average * $people + $official;
        // 返回结果
        return [$people, $money];
    }

    /**
     * 公示管理
     */
    public function notice(Request $req)
    {
        // 提交请求
        if ($req->isPost()) {
            // 行为动作
            $action = $req->param('action');
            if ($action == 'compute') {
                try {
                    // 人均支付
                    $average = $req->param('average', 0);
                    // 官方补齐
                    $official = $req->param('official', 0);
                    // 计算金额
                    list($people, $money) = $this->compute($average, $official);
                    // 返回结果
                    return json([
                        'code'      =>  200,
                        'message'   =>  '恭喜您、操作成功！',
                        'data'      =>  [
                            'people'    =>  $people,
                            'money'     =>  $money
                        ],
                    ]);
                } catch (\Exception $e) {
                    return json([
                        'code'      =>  500,
                        'message'   =>  $e->getMessage(),
                    ]);
                }
            } else if ($action == 'create') {
                try {
                    // 开启事务
                    Db::startTrans();
                    // 捐赠对象
                    $target = $req->param('target');
                    if (empty($target)) {
                        throw new \think\Exception("很抱歉、请输入捐赠对象的账号！");
                    }
                    // 获取图片
                    $file = $req->file('image');
                    if (empty($file)) {
                        throw new \think\Exception("很抱歉、请提供图片！");
                    }
                    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
                    if (!$info) {
                        throw new \think\Exception($file->getError());
                    }
                    $image = $info->getSaveName();
                    // 获取原因
                    $reason = $req->param('reason');
                    if (empty($reason)) {
                        throw new \think\Exception("很抱歉、请输入原因！");
                    }
                    // 获取详情
                    $content = $req->param('content');
                    // 人均支付
                    $average = $req->param('average', 0);
                    // 官方补齐
                    $official = $req->param('official', 0);
                    // 计算金额
                    list($people, $money) = $this->compute($average, $official);
                    // 添加公示
                    $id = Db::table('fund_notice')->insertGetId([
                        'fund'      =>  0,
                        'image'     =>  $image,
                        'username'  =>  $target,
                        'money'     =>  $money,
                        'reason'    =>  $reason,
                        'people'    =>  $people,
                        'content'   =>  $content,
                        'create_at' =>  $this->timestamp,
                        'update_at' =>  $this->timestamp,
                    ]);
                    if (empty($id)) {
                        throw new \think\Exception("很抱歉、发布公示失败！");
                    }
                    // 当前时间
                    $date = $this->timestamp;
                    // 普通用户需要扣钱
                    if ($average > 0) {
                        // 找出符合条件的用户
                        $usernames = Db::table('fund_holder')->where('money', '>=', $average)->where('username', '<>', '00000000000')->column('username');
                        if (empty($usernames)) {
                            throw new \think\Exception("很抱歉、没有找到符合条件的用户！");
                        }
                        // 日志记录
                        $logs = [];
                        // 循环更新
                        $sql = 'UPDATE `fund_holder` SET `money` = CASE `username` ';
                        foreach ($usernames as $key => $username) {
                            // 修改语句
                            $sql .= " WHEN '" . $username . "' THEN `money` - $average ";
                            // 日志信息
                            $logs[] = [
                                'fund'      =>  0,
                                'notice'    =>  $id,
                                'action'    =>  6,
                                'username'  =>  $username,
                                'friend'    =>  null,
                                'product'   =>  0,
                                'price'     =>  0,
                                'money'     =>  -$average,
                                'create_at' =>  $date
                            ];
                        }
                        // 修改语句
                        $sql .= " ELSE `money` END, `update_at` = CASE WHEN `username` IN('" . implode("', '", $usernames) . "') THEN '$date' ELSE `update_at` END;";
                        // 修改金额
                        $bool = Db::execute($sql);
                        if (empty($bool)) {
                            throw new \think\Exception("很抱歉、修改用户资金失败！");
                        }
                        // 添加记录
                        $bool = Db::table('fund_log')->insertAll($logs);
                        if (empty($bool)) {
                            throw new \think\Exception("很抱歉、日志记录失败！");
                        }
                    }
                    // 官方账号需要扣钱
                    if ($official > 0) {
                        // 修改资金
                        $bool = Db::table('fund_holder')->where('username', '=', '00000000000')->update([
                            'money'     =>  Db::raw('money-' . $official),
                            'update_at' =>  $date
                        ]);
                        if (empty($bool)) {
                            throw new \think\Exception("很抱歉、官方账号资金扣除失败！");
                        }
                        // 添加日志
                        $log = [
                            'fund'      =>  0,
                            'notice'    =>  $id,
                            'action'    =>  6,
                            'username'  =>  '00000000000',
                            'friend'    =>  null,
                            'product'   =>  0,
                            'price'     =>  0,
                            'money'     =>  -$official,
                            'create_at' =>  $date
                        ];
                        $bool = Db::table('fund_log')->insert($log);
                        if (empty($bool)) {
                            throw new \think\Exception("很抱歉、日志记录失败！");
                        }
                    }
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                    exit;
                }
                // 操作成功
                $this->success('恭喜您、操作成功！');
                exit;
            } else if ($action == 'update') {
                // 获取编号
                $id = $req->param('id');
                if (empty($id)) {
                    $this->error('很抱歉、请提供编号！');
                    exit;
                }
                // 查询数据
                $notice = Db::table('fund_notice')->where('id', '=', $id)->find();
                if (empty($notice)) {
                    $this->error('很抱歉、该信息不存在！');
                    exit;
                }
                // 获取图片
                $file = $req->file('image');
                $image = null;
                if (!empty($file)) {
                    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
                    if (!$info) {
                        $this->error($file->getError());
                        exit;
                    }
                    $image = $info->getSaveName();
                }
                // 获取原因
                $reason = $req->param('reason');
                // 获取详情
                $content = $req->param('content');
                // 更新数据
                $data = [
                    'reason'    =>  $reason,
                    'content'   =>  $content,
                    'update_at' =>  $this->timestamp
                ];
                if (!empty($image)) {
                    $data['image'] = $image;
                }
                $bool = Db::table('fund_notice')->where('id', '=', $id)->update($data);
                if (empty($bool)) {
                    $this->error('很抱歉、更新失败请重试！');
                    exit;
                }
                // 操作成功
                $this->success('恭喜您、操作成功！');
                exit;
            } else if ($action == 'get') {
                // 获取编号
                $id = $req->param('id');
                if (empty($id)) {
                    return json([
                        'code'      =>  500,
                        'message'   =>  '很抱歉、请提供编号！'
                    ]);
                }
                // 查询数据
                $notice = Db::table('fund_notice')->where('id', '=', $id)->find();
                if (empty($notice)) {
                    return json([
                        'code'      =>  500,
                        'message'   =>  '很抱歉、该信息不存在！'
                    ]);
                }
                // 返回数据
                return json([
                    'code'      =>  200,
                    'message'   =>  '恭喜您、操作成功！',
                    'data'      =>  $notice
                ]);
            }
        }
        // 查询对象
        $query = Db::table('fund_notice');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', '=', $username);
        }
        // 查询数据
        $notices = $query->order('create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('notices', $notices);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 配置保存
     */
    public function config(Request $req)
    {
        // 获取配置
        $config = Configure::get('hello.fund');
        $config = empty($config) ? [] : $config;
        // 获取参数
        $param = $req->param();
        $param['enable'] = empty($param['enable']) ? false : true;
        // 合并参数
        $config = array_merge($config, $param);
        // 保存设置
        try {
            Configure::set('hello.fund', $config);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 基金记录
     */
    public function logs(Request $req)
    {
        // 查询对象
        $query = Db::table('fund_log')->alias('fl')
            ->leftJoin('store s', 's.id = fl.product')
            ->leftJoin('fund_notice fn', 'fn.id = fl.notice')
            ->field('s.title AS product_title, fn.username AS notice_username, fn.reason AS notice_reason, fl.*');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('fl.username', '=', $username);
        }
        // 查询数据
        $logs = $query->order('fl.create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('logs', $logs);
        // 显示页面
        return $this->fetch();
    }
}
