<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\facade\Env;
use app\index\controller\Configure;

class Event extends Base
{

    /**
     * 立即发货
     */
    public function shipped(Request $req)
    {
        // 获取编号
        $id = $req->param('id/d');
        if (empty($id)) {
            $this->error('很抱歉、请提供编号！');
            exit;
        }
        // 查询记录
        $log = Db::table('event_log')->where('id', '=', $id)->find();
        if (empty($log)) {
            $this->error('很抱歉、该信息不存在！');
            exit;
        }
        // 获取内容
        $send = $req->param('send');
        if (empty($send)) {
            $this->error('很抱歉、发货信息不能为空！');
            exit;
        }
        // 更新数据
        $bool = Db::table('event_log')->where('id', '=', $id)->update([
            'status'        =>  \app\index\controller\Event::STATUS_SHIPPED,
            'send'          =>  $send,
            'update_at'     =>  $this->timestamp,
        ]);
        if (empty($bool)) {
            $this->error('很抱歉、操作失败请再试一次！');
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }


    /**
     * 删除记录
     */
    public function remove(Request $req)
    {
        // 获取编号
        $id = $req->param('id/d');
        if (empty($id)) {
            $this->error('很抱歉、请提供编号！');
            exit;
        }
        // 查询记录
        $log = Db::table('event_log')->where('id', '=', $id)->find();
        if (empty($log)) {
            $this->error('很抱歉、该信息不存在！');
            exit;
        }
        // 删除数据
        $bool = Db::table('event_log')->where('id', '=', $id)->delete();
        if (empty($bool)) {
            $this->error('很抱歉、操作失败请再试一次！');
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 刮刮卡
     */
    public function scratch(Request $req)
    {
        // 查询对象
        $query = Db::table('event_log');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', '=', $username);
        }
        // 条件：按状态搜索
        $status = $req->param('status');
        if (!is_null($status) && strlen($status)) {
            $query->where('status', '=', $status);
        }
        // 条件：按中奖搜索
        $hit = $req->param('hit');
        if (!is_null($hit) && strlen($hit)) {
            $query->where('hit', '=', $hit);
        }
        // 条件：按奖品类型搜索
        $reward_type = $req->param('reward_type');
        if (!is_null($reward_type) && strlen($reward_type)) {
            $query->where('reward_type', '=', $reward_type);
        }
        // 条件：按具体奖品搜索
        $reward = $req->param('reward');
        if (!is_null($reward) && strlen($reward)) {
            $query->where('reward', '=', $reward);
        }
        // 获取奖品
        $rewards = config('hello.event.scratch.reward');
        // 搜索数据
        $logs = $query->order('update_at DESC')->paginate(20, false, ['query' => $req->param()])->each(function($item) use($rewards){
            $item['receive'] = empty($item['receive']) ? [] : json_decode($item['receive'], true);
            if (empty($item['reward'])) {
                $item['reward'] = [
                    'name'  =>  '<div class="text-muted">再接再厉</div>'
                ];
            } else {
                foreach ($rewards as $_key => $_item) {
                    if ($item['reward'] == $_item['id']) {
                        $item['reward'] = $_item;
                        break;
                    }
                }
            }
            return $item;
        });
        $this->assign('logs', $logs);
        // 数据统计
        $count = Db::table('event_log')->fieldRaw('reward, COUNT(id) AS count')->where('hit', '=', 1)->group('reward')->select();
        foreach ($count as $key => $item) {
            foreach ($rewards as $_key => $_item) {
                if ($item['reward'] == $_item['id']) {
                    $_item['name'] .= '(' . $item['count'] . ')';
                    $rewards[$_key] = $_item;
                    break;
                }
            }
        }
        $this->assign('rewards', $rewards);
        // 显示页面
        $this->assign('statuses', [
            '代提货', '待发货', '已发货'
        ]);
        $this->assign('hits', [
            '未中奖', '中奖啦'
        ]);
        $this->assign('types', [
            1   =>  '矿机',
            2   =>  '实物',
            3   =>  '话费',
            8   =>  '货币',
        ]);
        return $this->fetch();
    }

    /**
     * 共享矿池
     */
    public function pool(Request $req)
    {
        // 读取配置
        $config = Configure::get('hello.event.pool');
        // 提交修改
        if ($req->isPost()) {
            // 要修改的数据
            $data = [];
            // 修改类型
            $action = $req->param('action');
            if ($action == 'pool') {
                // 是否开启
                $data['enable'] = $req->param('enable/b');
                // 剩余容量
                $data['volume'] = $req->param('volume/f');
                // 目前难度
                $data['complexity'] = $req->param('complexity/d');
                // 收益比例
                $data['percent'] = $req->param('percent');
                // 收益浮动
                $data['float'] = $req->param('float');
                // 时间间隔
                $data['interval'] = $req->param('interval/d');
                // 背景图片
                $file = $req->file('background');
                if (!empty($file)) {
                    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
                    if (!$info) {
                        $this->error('很抱歉、' . $file->getError() . '！');
                        exit;
                    }
                    $data['background'] = '/upload/' . $info->getSaveName();
                }
            } else {
                // 获取名称
                $name = $req->param('name/a');
                // 获取算力
                $power = $req->param('power/a');
                // 获取价格
                $price = $req->param('price/a');
                // 获取每天限量
                $day = $req->param('day/a');
                // 获取每人限量
                $person = $req->param('person/a');
                // 循环处理
                $data = [
                    'prop'  =>  []
                ];
                foreach ($name as $key => $value) {
                    $data['prop'][] = [
                        'name'      =>  $name[$key],
                        'power'     =>  $power[$key] ?: 0,
                        'price'     =>  $price[$key] ?: 0,
                        'limit'     =>  [
                            'day'   =>  $day[$key] ?: 0,
                            'person'   =>  $person[$key] ?: 0,
                        ],
                    ];
                }
            }
            // 保存设置
            try {
                Configure::set('hello.event.pool', $data);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit;
            }
            // 修改成功
            $this->success('恭喜您、操作成功！', '/admin/event/pool.html');
            exit;
        }
        // 查询对象
        $query = Db::table('pool');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', '=', $username);
        }
        // 条件：按操作类型搜索
        $action = $req->param('action');
        if (!is_null($action) && strlen($action)) {
            $query->where('action', '=', $action);
        }
        // 条件：按具体道具搜索
        $prop = $req->param('prop');
        if (!is_null($prop) && strlen($prop)) {
            $query->where('prop', '=', $prop);
        }
        // 搜索数据
        $logs = $query->where('action', '<>', 3)->order('create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('logs', $logs);
        // 查询道具
        $props = Db::table('store')->field('id, title')->where('catalog', '=', 2)->order('sort DESC')->select();
        $this->assign('props', $props);
        // 显示页面
        $this->assign('actions', [
            1   =>  '领取收益',
            2   =>  '使用道具',
        ]);
        $this->assign('config', $config);
        return $this->fetch();
    }

    /**
     * 创业众筹 - 商店
     */
    public function funding(Request $req)
    {
        // 系统配置
        $config = config('hello.funding');
        // 提交请求
        if ($req->isPost()) {
            // 获取动作
            $action = $req->param('action');
            // 众筹对象
            $fd = new \app\index\controller\Funding();
            // 获取数据
            if ($action == 'get') {
                $id = $req->param('id/d');
                if (empty($id)) {
                    return json([
                        'code'      =>  501,
                        'message'   =>  '很抱歉、请提供编号！'
                    ]);
                }
                $obj = $fd->get($id);
                if (empty($obj)) {
                    return json([
                        'code'      =>  502,
                        'message'   =>  '很抱歉、该项目不存在！'
                    ]);
                }
                return json([
                    'code'      =>  200,
                    'message'   =>  '恭喜您、操作成功！',
                    'data'      =>  $obj
                ]);
            }
            // 商品编号
            $id = $req->param('id/d');
            if ($action == 'update' && empty($id)) {
                $this->error('很抱歉、请刷新页面再试一次！');
                exit;
            }
            // 项目名称
            $title = $req->param('title');
            if (empty($title)) {
                $this->error('很抱歉、请提供项目名称！');
                exit;
            }
            // 目标金额
            $target = $req->param('target/f');
            if (empty($target)) {
                $this->error('很抱歉、请提供目标金额！');
                exit;
            }
            // 项目分类
            $catalog = $req->param('catalog/d') ?: 1;
            // 项目类型
            $type = $req->param('type/d') ?: 1;
            // 商品详情
            $content = $req->param('content');
            // 图片地址
            $file = $req->file('image');
            $image = null;
            if ($action == 'create' && empty($file)) {
                $this->error('很抱歉、请选择项目图片');
                exit;
            } else {
                if (!empty($file)) {
                    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
                    if (!$info) {
                        $this->error('很抱歉、' . $file->getError() . '！');
                        exit;
                    }
                    $image = $info->getSaveName();
                }
            }
            // 项目详情
            $content = $req->param('content');
            try {
                $data = [
                    'type'      =>  $type,
                    'catalog'   =>  $catalog,
                    'title'     =>  $title,
                    'target'    =>  $target,
                    'content'   =>  $content,
                ];
                if (!empty($image)) {
                    $data['image'] = $image;
                }
                // 项目发起人
                $owner = $req->param('owner');
                // 添加项目
                if ($action == 'create') {
                    // 到期时间
                    $data['expire_at'] = date('Y-m-d H:i:s', strtotime('+' . $config['expire'] . ' day'));
                    // 项目发起人
                    if (!empty($owner)) {
                        $data['owner'] = $owner;
                    }
                    // 创建数据
                    $fd->create($data);
                } else {
                    // 项目类型
                    $data['status'] = $req->param('status/d') ?: 1;
                    // 排列顺序
                    $data['sort'] = $req->param('sort/d') ?: 0;
                    // 是否可见
                    $data['visible'] = $req->param('visible/d') ?: 0;
                    // 项目发起人
                    $data['owner'] = $owner;
                    // 到期时间
                    $expire_at = $req->param('expire_at');
                    if (!empty($expire_at)) {
                        $data['expire_at'] = $expire_at;
                    }
                    // 更新数据
                    $fd->update($id, $data);
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit;
            }
            // 操作成功
            $this->success('恭喜您、操作成功！');
            exit;
        }
        // 查询对象
        $query = Db::table('funding');
        // 搜索数据
        $logs = $query->order('sort DESC, current DESC, people DESC, count DESC, update_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('logs', $logs);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 创业众筹 - 记录
     */
    public function funding_log(Request $req)
    {
        // 项目列表
        $fids = Db::table('funding')->field('id, title')->order('status ASC, sort DESC')->select();
        $this->assign('fids', $fids);
        // 查询对象
        $query = Db::table('funding_log')->alias('l')->join('funding f', 'f.id = l.fid')
                        ->field('f.title, l.*');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('l.username', '=', $username);
        }
        // 条件：按具体项目搜索
        $fid = $req->param('fid');
        if (!is_null($fid) && strlen($fid)) {
            $query->where('l.fid', '=', $fid);
        }
        // 搜索数据
        $logs = $query->order('l.create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('logs', $logs);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 票券列表
     */
    public function ticket(Request $req)
    {
        // 操作类型
        $action = $req->param('action');
        if ($action == 'create') {
            try {
                // 生成类型
                $type = $req->param('type', 1);
                // 生成数量
                $number = $req->param('number/d', 1);
                // 创建数据
                $count = (new \app\index\controller\Ticket())->create($type, $number);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit;
            }
            $this->success('恭喜您、成功生成' . $count . '个票券！');
            exit;
        } else if ($action == 'remove') {
            try {
                // 删除数据
                (new \app\index\controller\Ticket())->delete($req->param('token'));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                exit;
            }
            $this->redirect('/admin/event/ticket.html');
            exit;
        }
        // 票券类型
        $types = Db::table('ticket')->field('type')->group('type')->select();
        $this->assign('types', $types ?: []);
        // 查询对象
        $query = Db::table('ticket');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', '=', $username);
        }
        // 条件：按类型搜索
        $type = $req->param('type');
        if (!is_null($type) && strlen($type)) {
            $query->where('type', '=', $type);
        }
        // 条件：按号码搜索
        $token = $req->param('token');
        if (!is_null($token) && strlen($token)) {
            $query->where('token', 'like', "%$token%");
        }
        // 查询数据
        $logs = $query->order('create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('logs', $logs);
        // 显示页面
        return $this->fetch();
    }
}
