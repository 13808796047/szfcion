<?php

namespace app\index\controller;

use think\Db;
use think\Request;

class Machine extends Base
{
    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------

    /**
     * 生成MID
     */
    public function generateMID()
    {
        $prefix = chr(mt_rand(65, 90));
        do {
            $number = mt_rand(1000000, 9999999);
            $mid = $prefix . $number;
        } while (!empty(Db::table('uuid')->where('id', '=', $mid)->find()));
        $bool = Db::table('uuid')->insert(['id' => $mid, 'type' => 2]);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、矿机编号生成失败！");
        }
        return $mid;
    }

    /**
     * 计算收益
     */
    public function compute($machine)
    {
        // 当前时间
        $now = time();
        // 每小时产量
        $yield = money($machine['income'] / $machine['cycle']);
        // 创建时间
        $create_at = strtotime($machine['create_at']);
        // 过期时间
        $expire_at = $create_at + $machine['cycle'] * 3600;
        // 上次领矿时间
        $profit_at = $machine['profit_at'] ? strtotime($machine['profit_at']) : $create_at;
        // 间隔时间
        $interval = $now - $profit_at;
        // 还没有领完、且超过了间隔时间
        if ($machine['profit'] < $machine['income'] && $interval >= $machine['interval']) {
            // 当前时间已经超过了过期时间、应该能将剩余的收益全部领取完
            if ($now > $expire_at) {
                return $machine['income'] - $machine['profit'];
            }
            // 最少需要间隔一个小时
            if ($interval >= 3600) {
                // 之前没领过
                if ($profit_at == $create_at) {
                    $start = $create_at;
                } else {
                    $start = $create_at + $machine['profit'] / $yield * 3600;
                }
                // 从创建时间起，一个小时后才能开始领取
                $start += 3600;
                // 从开始时间 + 3600 * n ，但是要小于当前时间
                $count = 0;
                for (; $start <= $expire_at && $start < $now; $start += 3600) {
                    $count++;
                }
                // 判断结果
                $result = $count * $yield;
                if ($machine['profit'] + $result > $machine['income']) {
                    $result = $machine['income']- $machine['profit'];
                }
                // 返回结果
                return $result;
            }
        }
        // 没有矿可以领
        return 0;
    }

    /**
     * 计算收益，测试用的
     */
    public function compute2($now, $create_at, $profit_at, $income, $cycle, $profit = 0)
    {
        // 当前时间
        $now = strtotime($now);
        // 每小时产量
        $yield = money($income / $cycle);
        // 创建时间
        $create_at = strtotime($create_at);
        // 过期时间
        $expire_at = $create_at + $cycle * 3600;
        // 上次领矿时间
        $profit_at = $profit_at ? strtotime($profit_at) : $create_at;
        // 间隔时间
        $interval = $now - $profit_at;
        // 还没有领完
        if ($profit < $income) {
            // 最少需要间隔一个小时
            if ($interval >= 3600) {
                // 之前没领过
                if ($profit_at == $create_at) {
                    $start = $create_at;
                } else {
                    $start = $create_at + $profit / $yield * 3600;
                }
                // 从创建时间起，一个小时后才能开始领取
                $start += 3600;
                // 从开始时间 + 3600 * n ，但是要小于当前时间
                $count = 0;
                for (; $start <= $expire_at && $start < $now; $start += 3600) {
                    $count++;
                }
                // 判断结果
                $result = $count * $yield;
                if ($profit + $result > $income) {
                    $result = $income- $profit;
                }
                // 返回结果
                return $result;
            }
        }
        // 没有矿可以领
        return 0;
    }

    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------

    /**
     * 创建矿机
     * @param   $source     来源，1：购买，2：赠送
     */
    public function create($username, $product, $source = 1)
    {
        // 矿机数据
        $data = [
            'mid'           =>  $this->generateMID(),
            'status'        =>  1,
            'source'        =>  $source,
            'username'      =>  $username,
            'product'       =>  $product['id'],
            'divide'        =>  $product['divide'],
            'name'          =>  $product['title'],
            'cycle'         =>  $product['cycle'],
            'income'        =>  $product['income'],
            'power'         =>  $product['power'],
            'interval'      =>  $product['interval'],
            'price'         =>  $product['price'],
            'profit'        =>  0,
            'count'         =>  0,
            'profit_at'     =>  null,
            'create_at'     =>  $this->timestamp,
            'update_at'     =>  $this->timestamp,
        ];
        // 添加矿机
        $bool = Db::table('machine')->insert($data);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、矿机初始化失败！");
        }
        // 账户对象
        $ac = new Account();
        // 自己的仪表盘更新
        $ac->dashboard($username, [
            'machine_count' =>  Db::raw('machine_count+1'),
            'machine_power' =>  Db::raw('machine_power+'.$product['power']),
            'power'         =>  Db::raw('power+'.$product['power']),
        ]);
        // 上级的仪表盘更新
        $myInviter = $ac->value($username, 'inviter');
        if (!empty($myInviter)) {
            if (empty(config('hello.store.machine.rebate')) || $source == 2) {
                // 不存在佣金、只需更新上级算力
                $this->team_power($myInviter, $product['power']);
            } else {
                // 存在佣金、需要更新上级算力和佣金
                $this->team_power_commission($myInviter, $product['power'], $product['price']);
            }
        }
    }

    /**
     * 递归：团队算力
     * @param  $power  矿机的算力
     * @param  $index  层级，最多8层
     */
    public function team_power($username, $power, $index = 1)
    {
        // 账户对象
        $ac = new Account();
        // 我的资料
        $user = $ac->value($username, ['type', 'inviter']);
        // 我能拿到的算力
        $team_power = config('hello.level.' . $user['type'] . '.team_power');
        if (!empty($team_power) && $team_power > 0) {
            // 要增加的算力
            $myPower = money($team_power * $power);
            if ($myPower > 0) {
                // 更新算力
                $ac->dashboard($username, [
                    'power'         =>  Db::raw('power+' . $myPower),
                    'team_power'    =>  Db::raw('team_power+' . $myPower),
                ]);
            }
        }
        // 获取上级的信息
        if ($index <= 8 && !empty($user['inviter'])) {
            $this->team_power($user['inviter'], $power, $index + 1);
        }
    }

    /**
     * 递归：团队算力、上级收益
     * @param  $power  矿机的算力
     * @param  $price  矿机的价格
     * @param  $index  层级，最多8层
     */
    public function team_power_commission($username, $power, $price, $index = 1)
    {
        // 账户对象
        $ac = new Account();
        // 我的资料
        $user = $ac->value($username, ['type', 'inviter']);
        // 我能拿到的佣金
        $commissions = config('hello.level.' . $user['type'] . '.commission');
        if (!empty($commissions) && array_key_exists($index, $commissions)) {
            // 存在条件的佣金
            if (is_array($commissions[$index])) {
                // 判断条件
                $array = $commissions[$index];
                if (array_key_exists('direct', $array)) {
                    // 查询我的直推有效人数
                    $my_direct_count = Db::table('account')->where('inviter', '=', $username)->where('authen', '=', 1)->count('id');
                    if ($my_direct_count >= $array['direct']) {
                        // 具体的佣金
                        $commission = $commissions[$index]['percent'] * $price;
                    } else {
                        $commission = 0;
                    }
                } else {
                    // 具体的佣金
                    $commission = $commissions[$index]['percent'] * $price;
                }
            } else {
                // 具体的佣金
                $commission = $commissions[$index] * $price;
            }
            // 如果存在佣金
            if ($commission > 0) {
                // 我的资料
                $userIns = $ac->instance($username);
                // 增加佣金
                (new Wallet())->change($username, 29, [
                    1   =>  [
                        $userIns['wallet']['money'],
                        $commission,
                        $userIns['wallet']['money'] + $commission
                    ],
                ]);
            }
        }
        // 我能拿到的算力
        $team_power = config('hello.level.' . $user['type'] . '.team_power');
        if (!empty($team_power) && $team_power > 0) {
            // 要增加的算力
            $myPower = money($team_power * $power);
            if ($myPower > 0) {
                // 更新算力
                $ac->dashboard($username, [
                    'power'         =>  Db::raw('power+' . $myPower),
                    'team_power'    =>  Db::raw('team_power+' . $myPower),
                ]);
            }
        }
        // 获取上级的信息
        if ($index <= 8 && !empty($user['inviter'])) {
            $this->team_power_commission($user['inviter'], $power, $price, $index + 1);
        }
    }

    /**
     * 递归：团队收益
     * @param  $index   第几层上级
     * @param  $profit  一键收矿时总共收了多少矿
     * @param  $divide  矿机最多允许几层上级可以获得收益
     */
    public function team_profit($inviter, $index, $profit, $divide)
    {
        // 在矿机的允许范围内
        if ($divide >= $index) {
            // 获取上级的信息
            $user = (new Account())->instance($inviter);
            // 上级级别
            $level = $user['account']['type'];
            // 查询该级别的配置
            $config = config('hello.level.'.$level);
            // 存在该级别的配置，并且该级别拥有团队收矿利益配置
            if (!empty($config) && !empty($config['profit'])) {
                // 拥有这一代的配置
                if (array_key_exists($index, $config['profit'])) {
                    // 我能拿多少比例
                    $profit_rate = $config['profit'][$index];
                    if ($profit_rate > 0) {
                        // 实际拿到的利润
                        $money = $profit_rate * $profit;
                        // 加钱
                        (new Wallet())->change($inviter, 30, [
                            1   =>  [$user['wallet']['money'], $money, $user['wallet']['money'] + $money]
                        ]);
                        // 继续给上级的上级加钱
                        if (!empty($user['account']['inviter'])) {
                            $this->team_profit($user['account']['inviter'], $index + 1, $profit, $divide);
                        }
                    }
                }
            }
        }
    }

    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

    /**
     * 我的矿机
     */
    public function index(Request $req)
    {
        // 我的账号
        $username = session('user.account.username');
        // 查询矿机
        $machines = Db::table('machine')
                    ->where('username', '=', $username)
                    ->where('status', '=', 1)
                    ->order('create_at DESC')->select();
        // 当前时间
        $now = time();
        // 总算力
        $power = 0;
        // 运行中的矿机
        $running = [];
        // 已过期的矿机
        $expire = [];
        // 当前可以领取的收益
        $profit = 0;
        // 循环矿机
        foreach ($machines as $key => $item) {
            // 剩余时间
            $start = strtotime($item['create_at']);
            $end = (($now - $start) / 60 / 60) / $item['cycle'];
            $rate = round($end * 100);
            $rate = $rate < 0 ? 0 : $rate;
            $item['rate'] = $rate;
            // 剩余时间的颜色
            if ($rate < 60) {
                $item['color'] = 'green';
            } else if ($rate >= 60 && $rate < 80) {
                $item['color'] = 'yellow';
            } else {
                $item['color'] = 'red';
            }
            // 总算力
            $power += $item['power'];
            if ($end >= 1) {
                $item['status'] = 0;
                $expire[] = $item;
            } else {
                // $yield += money($item['income'] / $item['cycle']);
                $running[] = $item;
            }
            // 计算可领取收益
            $profit += $this->compute($item);
        }
        // 领取记录
        $clocks = Db::table('clock')->alias('c')
                    ->join('machine m', 'm.mid = c.mid')
                    ->field('m.name, c.*')
                    ->where('c.username', '=', $username)
                    ->order('c.create_at DESC')->limit(20)->select();
        $this->assign('power', $power);
        $this->assign('profit', $profit);
        $this->assign('expire', $expire);
        $this->assign('running', $running);
        $this->assign('machines', $machines);
        $this->assign('clocks', $clocks);
        return $this->fetch();
    }

    /**
     * 一键收矿
     */
    public function profit(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 用户账号
            $username = session('user.account.username');
            // 账户对象
            $ac = new Account();
            // 查询账户
            $user = $ac->instance($username);
            if (empty($user)) {
                return json([
                    'code'      =>  501,
                    'message'   =>  '很抱歉、请重新登录！'
                ]);
            }
            if (empty($user['account']['status'])) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、您的账户已被冻结！'
                ]);
            }
            // 我的矿机
            $machines = Db::table('machine')
                        ->where('username', '=', $username)
                        ->where('status', '=', 1)
                        ->order('create_at DESC')->select();
            // 可领取收益
            $profit = 0;
            // 领取记录列表
            $logs = [];
            // 循环矿机
            foreach ($machines as $key => $machine) {
                // 计算收益
                $money = $this->compute($machine);
                // 存在可领取的收益
                if ($money > 0) {
                    // 累计总收益
                    $profit += $money;
                    // 更新矿机状态
                    $row = Db::table('machine')->where('mid', '=', $machine['mid'])->update([
                        'profit'    =>  Db::raw('profit+' . $money),
                        'count'     =>  Db::raw('count+1'),
                        'profit_at' =>  $this->timestamp,
                        'update_at' =>  $this->timestamp,
                    ]);
                    if (empty($row)) {
                        return json([
                            'code'      =>  503,
                            'message'   =>  '很抱歉、服务器繁忙请稍后再试！ -1',
                        ]);
                    }
                    // 保存领取日志
                    $logs[] = [
                        'username'      =>  $username,
                        'mid'           =>  $machine['mid'],
                        'money'         =>  $money,
                        'create_at'     =>  $this->timestamp,
                    ];
                    // 这是最后一次领取
                    if ($money + $machine['profit'] >= $machine['income']) {
                        $ac->dashboard($username, [
                            'machine_expire'    =>  Db::raw('machine_expire+1')
                        ]);
                    }
                    // 递归：团队收益
                    if (!empty($user['account']['inviter'])) {
                        $this->team_profit($user['account']['inviter'], 1, $money, $machine['divide']);
                    }
                }
            }
            // 没有矿可以领取
            if ($profit <= 0) {
                return json([
                    'code'      =>  504,
                    'message'   =>  '很抱歉、请过一段时间再来！',
                ]);
            }
            // 存在日志记录
            if (!empty($logs)) {
                // 保存领矿记录
                $row = Db::table('clock')->insertAll($logs);
                if ($row != count($logs)) {
                    return json([
                        'code'      =>  505,
                        'message'   =>  '很抱歉、服务器繁忙请稍后再试！ -2',
                    ]);
                }
            }
            // 存在可领矿石
            if ($profit > 0) {
                // 更新余额
                (new Wallet())->change($username, 21, [
                    1 => [
                        $user['wallet']['money'],
                        $profit,
                        $user['wallet']['money'] + $profit
                    ]
                ]);
            }
            // 操作日志
            $this->log(31, $profit);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  500,
                'message'   =>  $e->getMessage()
            ]);
        }
        // 操作成功
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！'
        ]);
    }
}
