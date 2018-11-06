<?php

namespace app\index\controller;

use think\Db;
use think\Request;

class Wallet extends Base
{

    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------

    /**
     * 生成RID
     */
    public function generateRID($multiple = 0)
    {
        $prefix = chr(mt_rand(65, 90));
        $prefix .= date('ymd');
        if ($multiple > 0) {
             do {
                $rids = [];
                for ($i = 0;$i < $multiple;$i++) {
                    $number = mt_rand(10000000, 99999999);
                    $rid = $prefix . $number;
                    $rids[] = $rid;
                }
            } while (!empty(Db::table('uuid')->where('id', 'in', $rids)->find()));
            $data = [];
            foreach ($rids as $key => $value) {
                $data[] = [
                    'id'    =>  $value,
                    'type'  =>  8,
                ];
            }
            $bool = Db::table('uuid')->insertAll($data);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、流水编号生成失败！");
            }
            return $rids;
        } else {
            do {
                $number = mt_rand(10000000, 99999999);
                $rid = $prefix . $number;
            } while (!empty(Db::table('uuid')->where('id', '=', $rid)->find()));
            $bool = Db::table('uuid')->insert(['id' => $rid, 'type' => 8]);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、流水编号生成失败！");
            }
            return $rid;
        }
    }

    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------

    /**
     * 资金调整
     * @param  $data  [ field => [before, now, after] ]
     * @param  $other 其他选项
     */
    public function change($username, $business, $data, $other = [])
    {
        // 货币配置
        $currencys = config('hello.currencys');
        // 流水编号
        $rid = $this->generateRID();
        $rows = [];
        $acData = [];
        // 循环数据
        foreach ($data as $cid => $item) {
            // 数据不对
            if (count($item) != 3) {
                throw new \think\Exception("很抱歉、资金流水数据错误！");
            }
            // 保存数据
            $rows[] = [
                'rid'       =>  $rid,
                'username'  =>  $username,
                'currency'  =>  $cid,
                'business'  =>  $business,
                'before'    =>  $item[0],
                'now'       =>  $item[1],
                'after'     =>  $item[2],
                'create_at' =>  $this->timestamp,
            ];
            // 货币不存在
            if (!array_key_exists($cid, $currencys)) {
                throw new \think\Exception("很抱歉、指定更新的货币不存在！ -" . $cid);
            }
            // 得到字段
            $field = $currencys[$cid]['field'];
            // 保存金额
            $acData[$field] = $item[1];
        }
        // 添加流水
        $bool = Db::table('record')->insertAll($rows);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、资金流水更新失败！");
        }
        // 更新金额
        $query = Db::table('wallet')->where('username', '=', $username);
        foreach ($acData as $key => $value) {
            if ($value >= 0) {
                $query->inc($key, $value);
            } else {
                $query->dec($key, -$value);
            }
        }
        // 业务金额
        if ($business == 20 || $business == 22) {
            // 商城购物
            if (array_key_exists('money', $acData)) {
                $query->inc('spend', -$acData['money']);
            }
        } else if ($business == 21) {
            // 矿机收益
            if (array_key_exists('money', $acData)) {
                $query->inc('profit', $acData['money']);
            }
        } else if ($business == 30) {
            // 团队矿机收益
            if (array_key_exists('money', $acData)) {
                $query->inc('team_profit', $acData['money']);
            }
        } else if ($business == 31) {
            // 团队交易分红
            if (array_key_exists('money', $acData)) {
                $query->inc('trade', $acData['money']);
            }
        } else if ($business == 32) {
            // 全球交易分红
            if (array_key_exists('money', $acData)) {
                $query->inc('bonus', $acData['money']);
            }
        } else if ($business == 10) {
            // 交易买入
            if (array_key_exists('money', $acData)) {
                $query->inc('buy', $acData['money']);
            }
        } else if ($business == 11 && array_key_exists('number', $other)) {
            // 交易卖出，仅在交易成功时，卖方冻结金额扣除的时候
            $query->inc('sell', $other['number']);
        } else if ($business == 13 && array_key_exists('number', $other)) {
            // 转账转出
            $query->inc('ts_out', $other['number']);
        } else if ($business == 14 && array_key_exists('money', $acData)) {
            // 转账转入
            $query->inc('ts_in', $acData['money']);
        }
        // 更新钱包
        $bool = $query->update(['update_at' => $this->timestamp]);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、钱包资金更新失败！");
        }
    }

    /**
     * 获取一个人的资金
     */
    public function get($username)
    {
        return Db::table('wallet')->field('username, rmb, money, deposit, release, score, score_deposit')->where('username', '=', $username)->find();
    }

    /**
     * 获取一批人的资金
     */
    public function pull($users)
    {
        $result = Db::table('wallet')->field('username, rmb, money, deposit, release, score, score_deposit')->where('username', 'in', $users)->select();
        $data = [];
        foreach ($result as $key => $item) {
            $data[$item['username']] = $item;
        }
        return $data;
    }

    /**
     * 批量调整 - 针对可用资金
     * @param  $data
     *         [
     *              [
     *                  'username'  =>  '',
     *                  'business'  =>  0,
     *                  'money'     =>  0,
     *              ]
     *         ]
     */
    public function batch($data)
    {
        // 用户列表
        $users = array_column($data, 'username');
        // 查询这批人当前的资金
        $userWallet = $this->pull($users);
        // 修改语句
        $update = 'UPDATE `wallet` SET `money` = CASE `username` ';
        // 循环数据
        foreach ($data as $key => $item) {
            // 当前金额
            $money = $item['money'];
            if ($money < 0 && array_key_exists($item['username'], $userWallet) && $money < -$userWallet[$item['username']]['money']) {
                $money = '-' . $userWallet[$item['username']]['money'];
            }
            // 修改语句
            $update .= " WHEN '" . $item['username'] . "' THEN `money` + $money ";
        }
        // 当前时间
        $date = $this->timestamp;
        // 修改语句
        $update .= " ELSE `money` END, `update_at` = CASE WHEN `username` IN('" . implode("', '", $users) . "') THEN '$date' ELSE `update_at` END;";
        // 执行修改
        $bool = Db::execute($update);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、钱包资金更新失败！");
        }
        // 批量获取编号
        $ids = $this->generateRID(count($data));
        // 要添加的数据
        $insertData = [];
        foreach ($data as $key => $item) {
            $before = 0;
            if (!array_key_exists($item['username'], $userWallet)) {
                throw new \think\Exception("很抱歉、找不到用户的钱包！");
            }
            $before = $userWallet[$item['username']]['money'];
            $insertData[] = [
                'rid'       =>  array_shift($ids),
                'username'  =>  $item['username'],
                'currency'  =>  1,
                'business'  =>  $item['business'],
                'before'    =>  $before,
                'now'       =>  $item['money'],
                'after'     =>  $before + $item['money'] < 0 ? 0 : $before + $item['money'],
                'create_at' =>  $date,
            ];
        }
        // 执行添加
        $bool = Db::table('record')->insertAll($insertData);
        if (empty($bool) || $bool != count($insertData)) {
            throw new \think\Exception("很抱歉、资金流水更新失败！");
        }
    }

    /**
     * 批量调整 - 针对冻结资金
     * @param  $data
     *         [
     *              [
     *                  'username'  =>  '',
     *                  'business'  =>  0,
     *                  'money'     =>  0,
     *              ]
     *         ]
     */
    public function batch_deposit($data)
    {
        // 用户列表
        $users = [];
        // 修改语句
        $update = 'UPDATE `wallet` SET `deposit` = CASE `username` ';
        // 循环数据
        foreach ($data as $key => $item) {
            // 当前金额
            $deposit = $item['deposit'];
            // 保存用户
            if (!in_array($item['username'], $users)) {
                $users[] = $item['username'];
            }
            // 修改语句
            $update .= " WHEN '" . $item['username'] . "' THEN `deposit` + $deposit ";
        }
        // 查询这批人当前的资金
        $userWallet = $this->pull($users);
        // 当前时间
        $date = $this->timestamp;
        // 修改语句
        $update .= " ELSE `deposit` END, `update_at` = CASE WHEN `username` IN('" . implode("', '", $users) . "') THEN '$date' ELSE `update_at` END;";
        // 执行修改
        $bool = Db::execute($update);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、钱包资金更新失败！");
        }
        // 批量获取编号
        $ids = $this->generateRID(count($data));
        // 要添加的数据
        $insertData = [];
        foreach ($data as $key => $item) {
            $before = 0;
            if (!array_key_exists($item['username'], $userWallet)) {
                throw new \think\Exception("很抱歉、找不到用户的钱包！");
            }
            $before = $userWallet[$item['username']]['deposit'];
            $insertData[] = [
                'rid'       =>  array_shift($ids),
                'username'  =>  $item['username'],
                'currency'  =>  2,
                'business'  =>  $item['business'],
                'before'    =>  $before,
                'now'       =>  $item['deposit'],
                'after'     =>  $before + $item['deposit'],
                'create_at' =>  $date,
            ];
        }
        // 执行添加
        $bool = Db::table('record')->insertAll($insertData);
        if (empty($bool) || $bool != count($insertData)) {
            throw new \think\Exception("很抱歉、资金流水更新失败！");
        }
    }

    /**
     * 批量释放资金
     */
    public function batch_release($usernames, $business, $percent)
    {
        // 查询这批人当前的资金
        $userWallet = $this->pull($usernames);
        // 修改语句
        $update = 'UPDATE `wallet` SET `release` = CASE `username` ';
        $update_money_sql = ', `money` = CASE `username` ';
        // 流水记录
        $recordData = [];
        // 当前时间
        $date = $this->timestamp;
        // 循环数据
        foreach ($userWallet as $key => $item) {
            // 用户账号
            $username = $item['username'];
            // 当前金额
            $money = $item['money'];
            $release = $item['release'];
            // 具体操作的金额
            $do_release = 0;
            $do_money = 0;
            if ($percent > 0 && $percent < 1) {
                $do_release = $release * $percent;
                $do_money = $release * $percent;
            } else if ($percent >= 1) {
                $do_release = $percent;
                $do_money = $percent;
            }
            // 不足则用剩余的
            if ($do_release > $release) {
                $do_release = $release;
                $do_money = $release;
            }
            // 修改语句
            $update .= " WHEN '$username' THEN `release` - $do_release ";
            $update_money_sql .= " WHEN '$username' THEN `money` + $do_money ";
            // 流水记录
            $recordData[] = [
                'username'  =>  $username,
                'currency'  =>  1,
                'business'  =>  $business,
                'before'    =>  $money,
                'now'       =>  $do_money,
                'after'     =>  $money + $do_money,
                'create_at' =>  $date,
            ];
            $recordData[] = [
                'username'  =>  $username,
                'currency'  =>  5,
                'business'  =>  $business,
                'before'    =>  $release,
                'now'       =>  -$do_release,
                'after'     =>  $release - $do_release,
                'create_at' =>  $date,
            ];
        }
        // 修改语句
        $update .= " ELSE `release` END ";
        $update .= $update_money_sql . " ELSE `money` END ";
        $update .= " , `update_at` = CASE WHEN `username` IN('" . implode("', '", $usernames) . "') THEN '$date' ELSE `update_at` END;";
        // 执行修改
        $bool = Db::execute($update);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、钱包资金更新失败！");
        }
        // 批量获取编号
        $ids = $this->generateRID(count($usernames));
        // 设置流水的编号
        $prevUser = null;
        $prevRid = null;
        foreach ($recordData as $key => $item) {
            if ($item['username'] != $prevUser) {
                $prevRid = array_shift($ids);
                $prevUser = $item['username'];
            }
            $item['rid'] = $prevRid;
            $recordData[$key] = $item;
        }
        // 执行添加
        $bool = Db::table('record')->insertAll($recordData);
        if (empty($bool) || $bool != count($recordData)) {
            throw new \think\Exception("很抱歉、资金流水更新失败！");
        }
    }

    // +----------------------------------------------------------------------
    // | 对外方法
    // +----------------------------------------------------------------------

    /**
     * 钱包首页
     */
    public function index(Request $req)
    {
        return $this->fetch();
    }

    /**
     * 流水记录
     */
    public function record(Request $req)
    {
        // 查询记录
        if ($req->isPost()) {
            // 用户账号
            $username = session('user.account.username');
            // 获取货币
            $cid = $req->param('cid/d', 1);
            // 当前货币
            $currency = config('hello.currencys.' . $cid);
            if (empty($currency)) {
                $currency = config('hello.currencys.1');
            }
            // 所有业务
            $businesses = array_intersect_key(config('hello.businesses'), array_fill_keys($currency['businesses'], true));
            // 分页数据
            $page = $req->param('page/d', 1);
            $size = $req->param('size/d', 20);
            $offset = $page - 1 < 0 ? 0 : ($page - 1) * $size;
            // 查询对象
            $query = Db::table('record')->where('username', '=', $username)->where('currency', '=', $cid);
            // 按类型查询
            $type = $req->param('type');
            if (is_null($type)) {
                $query->where('business', 'in', $currency['businesses']);
            } else {
                $query->where('business', '=', $type);
            }
            // 查询数据
            $list = $query->field('rid, business, now, create_at')
                    ->limit($offset, $size)->order('create_at DESC')
                    ->select();
            // 查询总额
            $total = $query->sum('now');
            // 返回结果
            return json([
                'code'              =>  200,
                'message'           =>  '恭喜您、操作成功！',
                'data'              =>  [
                    'unit'          =>  $currency['name'],
                    'total'         =>  $total,
                    'currency'      =>  $currency,
                    'businesses'    =>  $businesses,
                    'list'          =>  $list,
                ],
            ]);
        }
        // 显示页面
        return $this->fetch();
    }

    /**
     * 柜台
     */
    public function counter(Request $req)
    {
        // 用户资料
        $username = session('user.account.username');
        $user = (new Account())->instance($username);
        $this->assign('user', $user);
        // 充值配置
        $payment = Configure::get('hello.payment');
        $payment = empty($payment) ? [] : $payment;
        $this->assign('payment', $payment);
        // 提现配置
        $withdraw = Configure::get('hello.withdraw');
        $withdraw = empty($withdraw) ? [] : $withdraw;
        $this->assign('withdraw', $withdraw);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 资金释放
     */
    public function release(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 释放配置
            $config = Configure::get('hello.release');
            $config = empty($config) ? [] : $config;
            // 判断状态
            if (empty($config) || empty($config['enable'])) {
                throw new \think\Exception("很抱歉、系统尚未开启释放！");
            }
            // 释放金额不对
            if (empty($config['percent']) || $config['percent'] < 0) {
                throw new \think\Exception("很抱歉、释放金额不正确！");
            }
            // 获取小时
            $hour = array_key_exists('hour', $config) ? $config['hour'] : '0';
            // 获取天
            $day = array_key_exists('day', $config) ? $config['day'] : '1';
            // 当前世家
            $time = time();
            // 释放周期
            switch ($config['cycle']) {
                // 按天释放
                case 1:
                    // 开始时间
                    $start_at = date('Y-m-d');
                    // 今天凌晨
                    $base_at = $start_at . ' 00:00:00';
                    // 准确的开始时间
                    $start_at .= ' ' . $hour . ':00:00';
                    // 查询记录
                    $log = Db::table('record')->where('business', '=', 85)->where('create_at', '>=', $base_at)->find();
                    if (!empty($log)) {
                        throw new \think\Exception("很抱歉、今天已经释放过了！");
                    }
                    // 时间没到
                    if ($time < strtotime($start_at)) {
                        throw new \think\Exception("很抱歉、时间还没有到！");
                    }
                    break;
                // 按周释放
                case 2:
                    // 本周一
                    $base_at = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
                    $base_at .= ' 00:00:00';
                    // 开始时间
                    $start_at = strtotime($base_at) + ($day - 1) * 86400;
                    // 准确的开始时间
                    $start_at += $hour * 3600;
                    $start_at = date('Y-m-d H:i:s', $start_at);
                    // 查询记录
                    $log = Db::table('record')->where('business', '=', 85)->where('create_at', '>=', $base_at)->find();
                    if (!empty($log)) {
                        throw new \think\Exception("很抱歉、本周已经释放过了！");
                    }
                    // 时间没到
                    if ($time < strtotime($start_at)) {
                        throw new \think\Exception("很抱歉、时间还没有到！");
                    }
                    break;
                // 按月释放
                case 3:
                    // 开始时间
                    $start_at = date('Y-m');
                    // 本月1号
                    $base_at = $start_at . '-01 00:00:00';
                    // 准确的开始时间
                    $start_at .= '-' . $day . ' ' . $hour . ':00:00';
                    // 查询记录
                    $log = Db::table('record')->where('business', '=', 85)->where('create_at', '>=', $base_at)->find();
                    if (!empty($log)) {
                        throw new \think\Exception("很抱歉、本月已经释放过了！");
                    }
                    // 时间没到
                    if ($time < strtotime($start_at)) {
                        throw new \think\Exception("很抱歉、时间还没有到！");
                    }
                    break;
                // 其他情况
                default:
                    throw new \think\Exception("很抱歉、释放周期错误！");
                    break;
            }
            // 查询有释放金的人
            $usernames = Db::table('wallet')->where('release', '>', 0)->column('username');
            // 执行释放
            $this->batch_release($usernames, 85, $config['percent']);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  555,
                'message'   =>  $e->getMessage(),
            ]);
        }
        // 返回结果
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
        ]);
    }
}
