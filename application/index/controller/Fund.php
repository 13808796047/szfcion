<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use think\facade\Env;

class Fund extends Base
{

    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------

    /**
     * 官方捐赠
     */
    public function donation($username, $product, $price)
    {
        // 获取配置
        $config = Configure::get('hello.fund');
        if (!empty($config) && !empty($config['enable'])) {
            // 计算金额
            $money = 0;
            if (array_key_exists('percent', $config) && $config['percent'] > 0) {
                $money = $price * $config['percent'];
            }
            // 存在金额
            if ($money > 0) {
                // 基金记录
                $fund_log = [
                    'fund'      =>  0,
                    'action'    =>  1,
                    'username'  =>  $username,
                    'friend'    =>  null,
                    'product'   =>  $product,
                    'price'     =>  $price,
                    'money'     =>  $money,
                    'people'    =>  0,
                    'create_at' =>  $this->timestamp,
                ];
                $bool = Db::table('fund_log')->insert($fund_log);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、基金记录失败！");
                }
                // 基金持有
                $fund_holder = Db::table('fund_holder')->where('username', '=', '00000000000')->find();
                if (empty($fund_holder)) {
                    $fund_holder = [
                        'fund'      =>  0,
                        'username'  =>  '00000000000',
                        'money'     =>  $money,
                        'expire_at' =>  $this->timestamp,
                        'create_at' =>  $this->timestamp,
                        'update_at' =>  $this->timestamp,
                    ];
                    $bool = Db::table('fund_holder')->insert($fund_holder);
                    if (empty($bool)) {
                        throw new \think\Exception("很抱歉、基金持有失败！");
                    }
                } else {
                    // 持有数据
                    $holder_data = [
                        'money'     =>  Db::raw('money+' . $money),
                        'update_at' =>  $this->timestamp,
                    ];
                    // 更新数据
                    $bool = Db::table('fund_holder')->where('username', '=', '00000000000')->update($holder_data);
                    if (empty($bool)) {
                        throw new \think\Exception("很抱歉、基金持有数量更新失败！");
                    }
                }
            }
        }
    }

    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

    /**
     * 立即充值
     */
    public function recharge(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 获取配置
            $config = Configure::get('hello.fund');
            if (empty($config) || empty($config['enable'])) {
                throw new \think\Exception("很抱歉、系统尚未开启该模块！");
            }
            // 获取密码
            $safeword = $req->param('safeword');
            if (empty($safeword)) {
                throw new \think\Exception("很抱歉、请输入安全密码！");
            }
            // 用户账号
            $username = session('user.account.username');
            // 用户对象
            $ac = new Account();
            $user = $ac->instance($username, null, $safeword);
            if (empty($user)) {
                throw new \think\Exception("很抱歉、安全密码不正确！");
            }
            // 获取金额
            $money = $req->param('money');
            $moneyAllow = [];
            if (array_key_exists('money', $config) && !empty($config['money'])) {
                $moneyAllow = explode(',', $config['money']);
            }
            if (!empty($moneyAllow) && !in_array($money, $moneyAllow)) {
                throw new \think\Exception("很抱歉、充值数量不在范围内！");
            }
            if ($money <= 0) {
                throw new \think\Exception("很抱歉、错误的数量！");
            }
            if ($user['wallet']['money'] < $money) {
                throw new \think\Exception("很抱歉、您的资金不足！");
            }
            // 受赠人
            $donee = $user;
            // 对方账号
            $target = $req->param('target');
            if (!empty($target)) {
                // 查询对方
                $targetObj = $ac->instance($target);
                if (empty($targetObj)) {
                    throw new \think\Exception("很抱歉、对方账号不存在！");
                }
                // 更改受赠人
                $donee = $targetObj;
            }
            // 钱包对象
            $wl = new Wallet();
            // 自己扣钱
            $wl->change($username, 60, [
                1   =>  [
                    $user['wallet']['money'],
                    -$money,
                    $user['wallet']['money'] - $money,
                ],
            ]);
            // 基金记录
            $fund_log = [
                'fund'      =>  0,
                'username'  =>  $donee['account']['username'],
                'product'   =>  0,
                'price'     =>  0,
                'money'     =>  $money,
                'people'    =>  0,
                'create_at' =>  $this->timestamp,
            ];
            if ($donee['account']['username'] == $username) {
                $fund_log['action'] = 2;
                $fund_log['friend'] = null;
            } else {
                $fund_log['action'] = 3;
                $fund_log['friend'] = $username;
            }
            $bool = Db::table('fund_log')->insert($fund_log);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、基金记录失败！");
            }
            // 观察期
            $expire = array_key_exists('expire', $config) ? $config['expire'] : 0;
            $expire = $expire < 1 ? 1 : $expire;
            // 基金持有
            $fund_holder = Db::table('fund_holder')->where('username', '=', $donee['account']['username'])->find();
            if (empty($fund_holder)) {
                $fund_holder = [
                    'fund'      =>  0,
                    'username'  =>  $donee['account']['username'],
                    'money'     =>  $money,
                    'expire_at' =>  date('Y-m-d 00:00:00', strtotime('+' . $expire . ' day')),
                    'create_at' =>  $this->timestamp,
                    'update_at' =>  $this->timestamp,
                ];
                $bool = Db::table('fund_holder')->insert($fund_holder);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、基金持有失败！");
                }
            } else {
                // 持有数据
                $holder_data = [
                    'money'     =>  Db::raw('money+' . $money),
                    'update_at' =>  $this->timestamp,
                ];
                // 最低金额
                $min = array_key_exists('min', $config) ? $config['min'] : 0;
                if ($min > 0 && $fund_holder['money'] < $min) {
                    $holder_data['expire_at'] = date('Y-m-d 00:00:00', strtotime('+' . $expire . ' day'));
                }
                // 更新数据
                $bool = Db::table('fund_holder')->where('username', '=', $donee['account']['username'])->update($holder_data);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、基金持有数量更新失败！");
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  555,
                'message'   =>  $e->getMessage(),
            ]);
        }
        // 操作成功
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
        ]);
    }

    /**
     * 基金首页
     */
    public function index(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 基金总额
        $money = Db::table('fund_holder')->sum('money');
        $this->assign('money', $money);
        // 基金人数
        $count = Db::table('fund_holder')->count('id');
        $this->assign('count', $count);
        // 公示金额
        $noticeMoney = Db::table('fund_notice')->sum('money');
        $this->assign('noticeMoney', $noticeMoney);
        // 公示数量
        $noticeCount = Db::table('fund_notice')->count('id');
        $this->assign('noticeCount', $noticeCount);
        // 按天统计
        $days = Db::table('fund_log')->fieldRaw("DATE_FORMAT(create_at, '%Y-%m-%d') AS day, sum(money) AS money")->where('action', '<>', 6)->group('day')->select();
        for ($i = count($days); $i < 10; $i ++) {
            $item = count($days) ? $days[0] : ['day' => date('Y-m-d'), 'money' => 0];
            $date = date('Y-m-d', strtotime($item['day']) - 86400);
            array_unshift($days, [
                'day'      =>  $date,
                'money'    =>  0,
            ]);
        }
        $daysAxis = ['x'];
        $daysData = ['data1'];
        foreach ($days as $key => $item) {
            $daysAxis[] = $item['day'];
            $daysData[] = $item['money'];
        }
        $this->assign('daysAxis', json_encode($daysAxis));
        $this->assign('daysData', json_encode($daysData));
        // 规则文档
        $config = Configure::get('hello.fund');
        $this->assign('config', $config);
        // 我的互助
        $myHolder = Db::table('fund_holder')->where('username', '=', $username)->find();
        $this->assign('myHolder', $myHolder);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 基金公示
     */
    public function notice(Request $req)
    {
        // 查询数据
        $notices = Db::table('fund_notice')->alias('fn')
            ->leftJoin('profile p', 'p.username = fn.username')
            ->field('p.realname, fn.*')
            ->order('fn.create_at DESC')
            ->select();
        $this->assign('notices', $notices);
        // 返回结果
        return $this->fetch();
    }
}
