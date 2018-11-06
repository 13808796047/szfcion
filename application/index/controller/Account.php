<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use think\Validate;
use think\facade\Env;
use think\facade\Log;

class Account extends Base
{

    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------

    /**
     * 生成UID
     */
    public function generateUID()
    {
        $prefix = chr(mt_rand(65, 90));
        do {
            $number = mt_rand(100000, 999999);
            $uid = $prefix . $number;
        } while (!empty(Db::table('uuid')->where('id', '=', $uid)->find()));
        $bool = Db::table('uuid')->insert(['id' => $uid, 'type' => 1]);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、账户编号生成失败！");
        }
        return $uid;
    }

    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------

    /**
     * 是否存在
     */
    public function exists($username, $isUid = false)
    {
        if ($isUid) {
            $model = Db::table('account')->field('uid')->where('uid', '=', $username)->find();
        } else {
            $model = Db::table('account')->field('uid')->where('username', '=', $username)->find();
        }
        return empty($model) ? false : $model['uid'];
    }

    /**
     * 账户实例
     */
    public function instance($username, $password = null, $safeword = null, $session = false)
    {
        // 账户数据
        $account = [];
        // 查询对象
        $query = Db::table('account')->field('id, password, safeword, create_at, update_at', true)->where('username', '=', $username);
        // 按登录密码查询
        if (!is_null($password)) {
            $query->where('password', '=', encryption($password));
        }
        // 按安全密码查询
        if (!is_null($safeword)) {
            $query->where('safeword', '=', encryption($safeword));
        }
        // 找到账户
        $account = $query->find();
        if (empty($account)) {
            return null;
        }
        // 查询档案
        $profile = Db::table('profile')->field('id, create_at, update_at', true)->where('username', '=', $username)->find();
        // 查询钱包
        $wallet = Db::table('wallet')->field('id, create_at, update_at', true)->where('username', '=', $username)->find();
        // 查询仪表盘
        $dashboard = $this->dashboard($username);
        // 合并数据
        $data = [
            'account'   => $account,
            'profile'   => $profile,
            'wallet'    =>  $wallet,
            'dashboard' =>  $dashboard,
        ];
        // 会话保存
        if ($session) {
            session('user', $data);
        }
        // 返回数据
        return $data;
    }

    /**
     * UID 转 UserName
     */
    public function uid_to_username($uid)
    {
        return Db::table('account')->where('uid', '=', $uid)->value('username');
    }

    /**
     * 账户属性
     */
    public function value($username, $field)
    {
        if (is_array($field)) {
            return Db::table('account')->where('username', '=', $username)->field($field)->find();
        } else {
            return Db::table('account')->where('username', '=', $username)->value($field);
        }
    }

    /**
     * 获取一批人的上级
     */
    public function inviters($users)
    {
        return Db::table('account')->field('username, inviter')->where('username', 'in', $users)->select();
    }

    /**
     * 创建账户
     * 外部必须开启事务
     */
    public function create($username, $password, $safeword, $inviter = null, $oauth = null)
    {
        // 是否存在
        $bool = $this->exists($username);
        if (!empty($bool)) {
            throw new \think\Exception("很抱歉、该账户已经注册了！");
        }
        // 用户编号
        $uid = $this->generateUID();
        // 基本数据
        $account = [
            'uid'           =>  $uid,
            'type'          =>  0,
            'status'        =>  1,
            'username'      =>  $username,
            'authen'        =>  0,
            'inviter'       =>  $inviter,
        ];
        // 账户注册
        $bool = Db::table('account')->insert(array_merge($account, [
            'password'      =>  encryption($password ?: 123456),
            'safeword'      =>  encryption($safeword ?: 123456),
            'create_at'     =>  $this->timestamp,
            'update_at'     =>  $this->timestamp,
        ]));
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、账户注册失败请重试！");
        }
        // 存在第三方档案
        $oauth_profile = [
            'nickname'  =>  null,
            'avatar'    =>  null,
        ];
        if (!empty($oauth)) {
            // 查询档案并绑定
            $oauth_profile = (new Oauth())->bind($oauth, $username);
        }
        // 建立档案
        $profile = [
            'username'      =>  $username,
            'nickname'      =>  $oauth_profile['nickname'] ?: $uid,
            'avatar'        =>  $oauth_profile['avatar'],
            'wechat'        =>  $username,
            'qq'            =>  null,
            'alipay'        =>  null,
            'realname'      =>  null,
            'idcard'        =>  null,
            'authen_reason' =>  null,
            'bankname'      =>  null,
            'bankcard'      =>  null,
            'bankaddress'   =>  null,
        ];
        $bool = Db::table('profile')->insert(array_merge($profile, [
            'create_at'     =>  $this->timestamp,
            'update_at'     =>  $this->timestamp,
        ]));
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、档案建立失败请重试！");
        }
        // 创建钱包
        $wallet = [
            'username'      =>  $username,
            'money'         =>  0,
            'deposit'       =>  0,
            'score'         =>  0,
            'score_deposit' =>  0,
            'spend'         =>  0,
            'profit'        =>  0,
            'team_profit'   =>  0,
            'bonus'         =>  0,
            'trade'         =>  0,
            'sell'          =>  0,
            'buy'           =>  0,
        ];
        $bool = Db::table('wallet')->insert(array_merge($wallet, [
            'create_at'     =>  $this->timestamp,
            'update_at'     =>  $this->timestamp,
        ]));
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、钱包创建失败请重试！");
        }
        // 初始化仪表盘
        $dashboard = [
            'username'          =>  $username,
            'power'             =>  0,
            'team_power'        =>  0,
            'team_count'        =>  0,
            'machine_count'     =>  0,
            'machine_power'     =>  0,
            'machine_expire'    =>  0,
            'one'               =>  0,
            'two'               =>  0,
            'three'             =>  0,
            'four'              =>  0,
            'five'              =>  0,
            'six'               =>  0,
            'seven'             =>  0,
            'eight'             =>  0,
            'lv0'               =>  0,
            'lv1'               =>  0,
            'lv2'               =>  0,
            'lv3'               =>  0,
            'lv4'               =>  0,
            'lv5'               =>  0,
            'lv6'               =>  0,
            'lv7'               =>  0,
            'lv8'               =>  0,
        ];
        $bool = Db::table('dashboard')->insert(array_merge($dashboard, [
            'create_at'         =>  $this->timestamp,
            'update_at'         =>  $this->timestamp,
        ]));
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、初始化账户仪表盘请重试！");
        }
        // 注册需要审核
        $register_audit = config('hello.register_audit');
        if (!empty($register_audit)) {
            // 添加记录
            $bool = Db::table('account_audit')->insert([
                'status'    =>  0,
                'username'  =>  $username,
                'create_at' =>  $this->timestamp,
                'update_at' =>  $this->timestamp,
            ]);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、账户审核记录保存失败！");
            }
        }
        // 注册赠送矿机
        $register_machine = config('hello.register_machine');
        if (!empty($register_machine)) {
            foreach ($register_machine as $key => $product) {
                // 赠送矿机
                (new Store())->give($product, $username);
            }
        }
        // 存在上级
        if (!empty($inviter)) {
            // 递归：更新上级仪表盘、几代的人数更新，同时更新lv0的人数
            $this->dashboard_people($inviter, 1, true);
            // 递归：上级升级
            $this->upgrade($inviter);
        }
        // 合并数据
        $data = [
            'account'   =>  $account,
            'profile'   =>  $profile,
            'wallet'    =>  $wallet,
            'dashboard' =>  $dashboard,
        ];
        // 返回数据
        return $data;
    }

    /**
     * 修改账户
     * 外部必须开启事务
     */
    public function update($username, $data, $session = true)
    {
        // 更新时间
        $data['update_at'] = $this->timestamp;
        // 存在登录密码
        if (array_key_exists('password', $data)) {
            $data['password'] = encryption($data['password']);
        }
        // 存在安全密码
        if (array_key_exists('safeword', $data)) {
            $data['safeword'] = encryption($data['safeword']);
        }
        // 更新数据
        $row = Db::table('account')->where('username', '=', $username)->update($data);
        if (empty($row)) {
            throw new \think\Exception("很抱歉、账户更新失败请重试！");
        }
        // 会话更新
        if ($session) {
            $account = session('account') ?: [];
            $account = array_merge($account, $data);
            session('account', $account);
        }
        // 返回结果
        return true;
    }

    /**
     * 更新或查看仪表盘
     */
    public function dashboard($username, $data = null)
    {
        // 更新仪表盘
        if (!is_null($data)) {
            $data['update_at'] = $this->timestamp;
            $bool = Db::table('dashboard')->where('username', '=', $username)->update($data);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、仪表盘更新失败！");
            }
        } else {
            // 查询仪表盘
            $data = Db::table('dashboard')->field('id, create_at, update_at', true)->where('username', '=', $username)->find();
            // 返回数据
            return $data;
        }
        // 返回结果
        return true;
    }

    /**
     * 修改推荐人专用
     * 递归：上级仪表盘里的数据更新
     */
    public function dashboard_inviter($username, $origin, $level, $op = '+', $index = 1)
    {
        // 第几代的字段
        $fields = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'];
        // 存在这一代
        if (array_key_exists($index, $fields)) {
            // 我的资料
            $my = $this->value($username, ['type', 'inviter']);
            // 最终数据
            $data = [];
            // 代数更新
            foreach ($fields as $key => $field) {
                if ($key >= $index) {
                    $number = ($key == $index ? 1 : $origin[$fields[$key - $index]]);
                    $data[$field] = Db::raw($field . $op . $number);
                }
            }
            // 等级更新
            for ($i = 0; $i <= 8; $i++) {
                if ($i == $level) {
                    $data['lv' . $i] = Db::raw('lv' . $i . $op . ($origin['lv' . $i] + 1));
                } else {
                    $data['lv' . $i] = Db::raw('lv' . $i . $op . $origin['lv' . $i]);
                }
            }
            // 认证人数
            if (array_key_exists('authen', $origin)) {
                $data['authen'] = Db::raw('authen' . $op . $origin['authen']);
            }
            // 算力更新
            $powerPercent = config('hello.level.' . $my['type'] . '.team_power');
            $powerPercent = empty($powerPercent) ? 0 : $powerPercent;
            if ($powerPercent > 0) {
                $team_power = $origin['team_power'] + $origin['machine_power'] * $powerPercent;
                $data['team_power'] = Db::raw('team_power' . $op . $team_power);
            }
            // 执行修改
            $this->dashboard($username, $data);
            // 存在上级、继续
            if (!empty($my['inviter'])) {
                // 上级继续更新仪表盘
                $this->dashboard_inviter($my['inviter'], $origin, $level, $op, $index + 1);
            }
        }
    }

    /**
     * 递归：上级仪表盘里的人数更新
     */
    public function dashboard_people($username, $index = 1, $lv = false, $op = '+')
    {
        // 第几代的字段
        $fields = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight'];
        // 存在这一代
        if (array_key_exists($index, $fields)) {
            // 具体字段
            $field = $fields[$index];
            // 要更新的数据
            $data = [
                'team_count'    =>  Db::raw('team_count' . $op . '1'),
                $field          =>  Db::raw($field . $op . '1'),
            ];
            // Lv0
            if ($lv === true) {
                $lv = 0;
            }
            // 更新仪表盘
            if (is_int($lv)) {
                $data['lv' . $lv]    =   Db::raw('lv' . $lv . $op . '1');
                $this->dashboard($username, $data);
            }
            // 获取上级
            $inviter = $this->value($username, 'inviter');
            if (!empty($inviter)) {
                // 上级继续更新仪表盘
                $this->dashboard_people($inviter, $index + 1, $lv, $op);
            }
        }
    }

    /**
     * 递归：上级仪表盘里的级别更新
     * @param  $index   最多8层
     */
    public function dashboard_level($inviter, $oldLevel, $newLevel, $index = 1)
    {
        // 仪表盘更新
        $this->dashboard($inviter, [
            'lv' . $oldLevel    =>  Db::raw('lv' . $oldLevel . '-1'),
            'lv' . $newLevel    =>  Db::raw('lv' . $newLevel . '+1'),
        ]);
        // 存在上级
        if ($index < 8) {
            $parent = $this->value($inviter, 'inviter');
            if (!empty($parent)) {
                $this->dashboard_level($parent, $oldLevel, $newLevel, $index + 1);
            }
        }
    }

    /**
     * 账户升级
     * 外部必须开启事务
     */
    public function upgrade($username, $level = null, $force = false)
    {
        // 用户数据
        $user = $this->instance($username);
        // 将要升级的级别
        if (is_null($level)) {
            $level = $user['account']['type'] + 1;
        }
        // 级别配置
        $levels = config('hello.level');
        // 存在这个级别
        if (array_key_exists($level, $levels)) {
            // 当前级别的配置
            $config = $levels[$level];
            // 存在升级条件
            $isCondition = true;
            if (array_key_exists('condition', $config) && !empty($config['condition'])) {
                // 具体条件
                $condition = $config['condition'];
                // 检查：实名认证
                if (array_key_exists('authen', $condition) && $user['account']['authen'] != 1) {
                    // throw new \think\Exception("很抱歉、请先通过实名认证！");
                    $isCondition = false;
                }
                // 检查：直推人数
                if (array_key_exists('direct', $condition)) {
                    // 要求人数都是已认证的
                    if (array_key_exists('direct_authen', $condition) && !empty($condition['direct_authen'])) {
                        $directCount = Db::table('account')->where('inviter', '=', $username)->where('authen', '=', 1)->count('id');
                        $directCount = $directCount ?: 0;
                        if ($directCount < $condition['direct']) {
                            $isCondition = false;
                        }
                    } else {
                        if ($user['dashboard']['one'] < $condition['direct']) {
                            $isCondition = false;
                        }
                    }
                }
                // 检查：直推 下属成员
                if (array_key_exists('direct_lv', $condition)) {
                    // 统计下级级别
                    $lvCount = Db::table('account')->fieldRaw('type, COUNT(id) AS count')->where('inviter', '=', $username)->group('type')->select();
                    $lvData = [];
                    foreach ($lvCount as $key => $value) {
                        $lvData[$value['type']] = $value['count'];
                    }
                    ksort($lvData);
                    foreach ($lvData as $key => $value) {
                        foreach ($lvData as $_key => $_value) {
                            if ($_key > $key) {
                                $lvData[$key] += $_value;
                            }
                        }
                    }
                    // 循环条件
                    foreach ($condition['direct_lv'] as $key => $value) {
                        if (!array_key_exists($key, $lvData) || $lvData[$key] < $value) {
                            // throw new \think\Exception("很抱歉、您直推中的成员级别还未达标！");
                            $isCondition = false;
                            break;
                        }
                    }
                }
                // 检查：算力
                if (array_key_exists('power', $condition) && $user['dashboard']['power'] < $condition['power']) {
                    // throw new \think\Exception("很抱歉、您的算力还未达标！");
                    $isCondition = false;
                }
                // 检查：下属成员
                if (array_key_exists('member', $condition)) {
                    $lvData = [];
                    for ($i = 0;$i <= 8; $i++) {
                        $lvData[$i] = (int) $user['dashboard']['lv' . $i];
                    }
                    ksort($lvData);
                    foreach ($lvData as $key => $value) {
                        foreach ($lvData as $_key => $_value) {
                            if ($_key > $key) {
                                $lvData[$key] += $_value;
                            }
                        }
                    }
                    foreach ($condition['member'] as $key => $value) {
                        if ($lvData[$key] < $value) {
                            // throw new \think\Exception("很抱歉、您团队中的成员级别还未达标！");
                            $isCondition = false;
                            break;
                        }
                    }
                }
                // 检查：团队人数
                if (array_key_exists('people', $condition) && $user['dashboard']['team_count'] < $condition['people']) {
                    // throw new \think\Exception("很抱歉、您的团队人数还未达标！");
                    $isCondition = false;
                }
            }
            // 通过升级条件
            if ($force || $isCondition) {
                // 查询升级发奖记录
                $upgradeLog = Db::table('upgrade')->where('username', '=', $username)->where('level', '=', $level)->find();
                // 不存在这个级别的发奖记录
                if (empty($upgradeLog)) {
                    $upgradeData = [
                        'username'  =>  $username,
                        'level'     =>  $level,
                        'create_at' =>  $this->timestamp,
                    ];
                    // 存在奖励
                    if (array_key_exists('reward', $config)) {
                        // 具体奖励
                        $reward = $config['reward'];
                        // 奖励：可用资金
                        if (array_key_exists('money', $reward) && $reward['money'] > 0) {
                            $upgradeData['money'] = $reward['money'];
                            (new Wallet())->change(
                                $username, 50, [
                                    1 => [
                                        $user['wallet']['money'],
                                        $reward['money'],
                                        $user['wallet']['money'] + $reward['money']
                                    ]
                                ]
                            );
                        }
                        // 奖励：矿机
                        if (array_key_exists('machine', $reward) && !empty($reward['machine'])) {
                            $upgradeData['machine'] = json_encode($reward['machine']);
                            foreach ($reward['machine'] as $key => $product) {
                                // 赠送矿机
                                (new Store())->give($product, $username);
                            }
                        }
                        // 奖励：算力
                        if (array_key_exists('power', $reward) && $reward['power'] > 0) {
                            $upgradeData['power'] = $reward['power'];
                            $this->dashboard($username, [
                                'power'    =>  Db::raw('power+' . $reward['power']),
                            ]);
                        }
                    }
                    // 增加发奖记录
                    $row = Db::table('upgrade')->insert($upgradeData);
                    if (empty($row)) {
                        throw new \think\Exception("很抱歉、升级奖励发送失败！");
                    }
                }
                // 更新当前用户的级别
                $this->update($username, [
                    'type'  =>  $level
                ]);
                // 存在上级
                if (!empty($user['account']['inviter'])) {
                    // 递归：更新上级仪表盘
                    $this->dashboard_level($user['account']['inviter'], $user['account']['type'], $level);
                    // 递归：上级升级
                    $this->upgrade($user['account']['inviter']);
                }
                // 升级成功
                return true;
            }
        }
        // 升级失败
        return false;
    }

    /**
     * 档案属性调整
     */
    public function attrs($username, $data)
    {
        // 身份证号码
        if (array_key_exists('idcard', $data)) {
            // 查询身份证
            $bool = Db::table('profile')->where('idcard', '=', $data['idcard'])->where('username', '<>', $username)->find();
            if (!empty($bool)) {
                throw new \think\Exception("很抱歉、该身份证号码已经存在！");
            }
        }
        // 修改时间
        $data['update_at'] = $this->timestamp;
        // 修改数据
        $bool = Db::table('profile')->where('username', '=', $username)->update($data);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、档案数据更新失败！");
        }
    }

    /**
     * 账户登录
     */
    public function login($username, $password, $user = null)
    {
        if (is_null($user)) {
            $user = $this->instance($username, $password);
            if (empty($user)) {
                throw new \think\Exception("很抱歉、账户或密码不正确！");
            }
            if (empty($user['account']['status'])) {
                throw new \think\Exception("很抱歉、该账户已被冻结！");
            }
        }
        session('user', $user);
        return $user;
    }

    /**
     * 快速登录
     */
    public function quick_login($username)
    {
        $user = $this->instance($username);
        if (empty($user)) {
            throw new \think\Exception("很抱歉、账户或密码不正确！");
        }
        if (empty($user['account']['status'])) {
            throw new \think\Exception("很抱歉、该账户已被冻结！");
        }
        session('user', $user);
        return $user;
    }

    /**
     * 账户退出
     */
    public function quit()
    {
        session('user', null);
        session('staff', null);
        session(null);
    }

    // +----------------------------------------------------------------------
    // | 外部接口
    // +----------------------------------------------------------------------

    /**
     * 用户首页
     */
    public function index(Request $req)
    {
        // 配置信息
        $configData = Db::table('config')->select();
        $config = [];
        foreach ($configData as $key => $item) {
            $config[$item['token']] = json_decode($item['value'], true);
        }
        // 当前用户
        $username = session('user.account.username');
        $user = $this->instance($username, null, null, true);
        // 最新交易
        $tradeList = Db::table('trade')->alias('t')->leftJoin('profile p', 'p.username = t.owner')
            ->field('t.type, t.number, t.create_at, p.nickname')
            ->order('t.create_at DESC')
            ->limit(20)->select();
        $this->assign('tradeList', $tradeList);
        // 注册需要审核
        $register_audit = config('hello.register_audit');
        if (!empty($register_audit)) {
            $audit_status = Db::table('account_audit')->where('username', '=', $username)->value('status');
            if (!is_null($audit_status) && $audit_status != 1) {
                $this->assign('audit', $this->timestamp);
            }
        }
        // 最新新闻
        $news = Db::table('article')->field('id, image, title, date')->where('type', '<>', 8)->where('date', '<=', date('Y-m-d H:i:s'))->order('top DESC, sort DESC, date DESC')->limit(5)->select();
        $this->assign('news', $news);
        // 显示页面
        $this->assign('user', $user);
        $this->assign('config', $config);
        return $this->fetch();
    }

    /**
     * 数据同步
     */
    public function sync(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 获取账号
            $username = session('user.account.username');
            // 查询账号
            $type = $this->value($username, 'type');
            // 等级同步
            $levels = config('hello.level');
            for ($i = $type + 1; $i < count($levels); $i++) {
                $bool = $this->upgrade($username, $i);
                if (empty($bool)) {
                    break;
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  555,
                'message'   =>  $e->getMessage()
            ]);
        }
        // 同步成功
        return json([
            'code'      =>  200,
            'message'   =>  'SUCCESS'
        ]);
    }

    /**
     * 用户资料
     */
    public function profile(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 用户对象
        $user = $this->instance($username, null, null, true);
        if ($req->isPost()) {
            return json([
                'code'      =>  200,
                'message'   =>  '恭喜您、操作成功！',
                'data'      =>  $user
            ]);
        }
        $this->assign('user', $user);
        // 授权对象
        $oa = new Oauth();
        // QQ绑定
        $qqconfig = config('hello.oauth.qq');
        if (!empty($qqconfig) && !empty($qqconfig['enable']) && !empty($qqconfig['appid'])) {
            // 查询资料
            $qqprofile = $oa->find(Oauth::PLATFORM_QQ, $qqconfig['appid'], $username);
            $this->assign('qqprofile', $qqprofile);
        }
        // 显示页面
        return $this->fetch();
    }

    /**
     * 编辑资料
     */
    public function edit(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 个性昵称
        $nickname = $req->param('nickname');
        if (empty($nickname)) {
            return json([
                'code'      =>  501,
                'message'   =>  '很抱歉、请填写个性昵称！'
            ]);
        }
        // 微信账号
        $wechat = $req->param('wechat');
        if (empty($wechat) || strlen($wechat) < 5) {
            return json([
                'code'      =>  502,
                'message'   =>  '很抱歉、请填写微信账号！'
            ]);
        }
        // QQ号码
        $qq = $req->param('qq/d');
        if (empty($qq) || strlen($qq) < 5 || strlen($qq) > 11) {
            return json([
                'code'      =>  503,
                'message'   =>  '很抱歉、请填写QQ号码！'
            ]);
        }
        // 支付宝
        $alipay = $req->param('alipay');
        if (empty($alipay) || strlen($alipay) < 5) {
            return json([
                'code'      =>  504,
                'message'   =>  '很抱歉、请填写支付宝账号！'
            ]);
        }
        // 银行名称
        $bankname = $req->param('bankname');
        if (empty($bankname) || strlen($bankname) < 2) {
            return json([
                'code'      =>  505,
                'message'   =>  '很抱歉、请填写银行名称！'
            ]);
        }
        // 银行卡号
        $bankcard = $req->param('bankcard/d');
        if (empty($bankcard) || strlen($bankcard) < 6) {
            return json([
                'code'      =>  506,
                'message'   =>  '很抱歉、请填写银行卡号！'
            ]);
        }
        // 银行地址
        $bankaddress = $req->param('bankaddress');
        if (empty($bankaddress) || strlen($bankaddress) < 3) {
            return json([
                'code'      =>  507,
                'message'   =>  '很抱歉、请填写银行地址！'
            ]);
        }
        // 保存数据
        $data = [
            'nickname'      =>  $nickname,
            'wechat'        =>  $wechat,
            'qq'            =>  $qq,
            'alipay'        =>  $alipay,
            'bankname'      =>  $bankname,
            'bankcard'      =>  $bankcard,
            'bankaddress'   =>  $bankaddress,
        ];
        // 修改账户
        $this->attrs($username, $data);
        // 保存日志
        $this->log(7);
        // 返回结果
        return json([
            'code'          =>  200,
            'message'       =>  '恭喜您、操作成功！',
        ]);
    }

    /**
     * 我的头像
     */
    public function avatar(Request $req)
    {
        // 用户账号
        $username = session('account.username');
        // 获取头像
        $avatarFile = $req->file('avatar');
        $info = $avatarFile->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/avatar');
        if (!$info) {
            return json([
                'code'      =>  501,
                'message'   =>  '很抱歉、' . $avatarFile->getError() . '！',
            ]);
        }
        // 删除之前的头像
        if (!empty(session('account.avatar'))) {
            unlink(Env::get('root_path') . 'public/avatar/' . session('account.avatar'));
        }
        // 修改账户
        $data = [
            'avatar'        =>  $info->getSaveName(),
        ];
        $this->update($username, $data);
        // 返回结果
        return json([
            'code'          =>  200,
            'message'       =>  '恭喜您、操作成功！',
        ]);
    }

    /**
     * 修改密码
     */
    public function reset(Request $req)
    {
        if ($req->isPost()) {
            // 验证参数
            $rule = [
                'type'          =>  'require|number|between:1,2',
                'password'      =>  'require|length:6,32',
                'verify_code'   =>  'require|number|length:' . config('hello.sms.length'),
            ];
            $msg  = [
                'type'          =>  '很抱歉、修改类型不正确！',
                'password'      =>  '很抱歉、登录密码长度必须在6-32之间！',
                'verify_code'   =>  '很抱歉、短信验证码格式错误！',
            ];
            $validate = Validate::make($rule, $msg);
            if (!$validate->check($req->param())) {
                return json([
                    'code'      =>  501,
                    'message'   =>  $validate->getError(),
                ]);
            }
            // 检测短信
            $username = session('user.account.username');
            $verify_code = $req->param('verify_code');
            $Service = new Service();
            if (!$Service->smsCheck($username, $verify_code)) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、短信验证码不正确！',
                ]);
            }
            // 根据类型修改
            $type = $req->param('type');
            $data = [];
            if ($type == 1) {
                $data = ['password' => $req->param('password')];
            } else {
                $data = ['safeword' => $req->param('password')];
            }
            // 修改账户
            $this->update($username, $data);
            // 退出登录
            if ($type == 1) {
                // 保存日志
                $this->log(5);
                $this->quit();
            } else {
                // 保存日志
                $this->log(6);
            }
            // 返回结果
            return json([
                'code'          =>  200,
                'message'       =>  '恭喜您、操作成功！',
            ]);
        }
        return $this->fetch();
    }

    /**
     * 实名认证
     */
    public function authen(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 用户资料
        $user = $this->instance($username, null, null, true);
        $this->assign('user', $user);
        // 提交认证
        if ($req->isPost()) {
            // 验证参数
            $rule = [
                'nickname'      =>  'require',
                'realname'      =>  'require|chs|length:2,4',
                'idcard'        =>  'require|idCard',
            ];
            $msg  = [
                'nickname'      =>  '很抱歉、请填写个性昵称！',
                'realname'      =>  '很抱歉、真实姓名不正确！',
                'idcard'        =>  '很抱歉、身份证号码不正确！',
            ];
            $validate = Validate::make($rule, $msg);
            if (!$validate->check($req->param())) {
                return json([
                    'code'      =>  501,
                    'message'   =>  $validate->getError(),
                ]);
            }
            // 检测认证
            $authen = $user['account']['authen'];
            if ($authen == 1) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、您已认证通过请勿重复提交！',
                ]);
            } else if ($authen == 2) {
                return json([
                    'code'      =>  503,
                    'message'   =>  '很抱歉、您的认证信息正在审核中请耐心等待！',
                ]);
            }
            // 注册需要审核
            $register_audit = config('hello.register_audit');
            if (!empty($register_audit)) {
                $audit_status = Db::table('account_audit')->where('username', '=', $username)->value('status');
                if (!is_null($audit_status) && $audit_status != 1) {
                    return json([
                        'code'      =>  520,
                        'message'   =>  '很抱歉、您的账户尚未通过审核！',
                    ]);
                }
            }
            try {
                // 认证配置
                $config = config('hello.authentication');
                // 图片认证
                $certificate = null;
                if (array_key_exists('certificate', $config)) {
                    // 证件资料
                    $certificate = [];
                    // 存放目录
                    $folder = Env::get('root_path') . 'public/cert/';
                    is_dir($folder . date('Ymd')) or mkdir($folder . date('Ymd'), 0777, true);
                    // 正面图片
                    if (!empty($config['certificate']['front'])) {
                        $file = $req->file('front');
                        if (empty($file)) {
                            return json([
                                'code'      =>  510,
                                'message'   =>  '很抱歉、请提供身份证正面图片！',
                            ]);
                        } else {
                            // 图片检查
                            $info = $file->validate(['ext' => 'jpg,jpeg,png'])->check();
                            if (!$info) {
                                return json([
                                    'code'      =>  511,
                                    'message'   =>  '很抱歉、错误的身份证正面图片！'
                                ]);
                            }
                            // 图片压缩
                            $image = \think\Image::open($file);
                            $certificate['front'] = '/' . date('Ymd') . '/' . md5(time() . mt_rand()) . '.' . $image->type();
                            $image->thumb(640, 1136)->save($folder . $certificate['front']);
                        }
                    }
                    // 反面图片
                    if (!empty($config['certificate']['back'])) {
                        $file = $req->file('back');
                        if (empty($file)) {
                            return json([
                                'code'      =>  512,
                                'message'   =>  '很抱歉、请提供身份证反面图片！',
                            ]);
                        } else {
                            // 图片检查
                            $info = $file->validate(['ext' => 'jpg,jpeg,png'])->check();
                            if (!$info) {
                                return json([
                                    'code'      =>  513,
                                    'message'   =>  '很抱歉、错误的身份证反面图片！'
                                ]);
                            }
                            // 图片压缩
                            $image = \think\Image::open($file);
                            $certificate['back'] = '/' . date('Ymd') . '/' . md5(time() . mt_rand()) . '.' . $image->type();
                            $image->thumb(640, 1136)->save($folder . $certificate['back']);
                        }
                    }
                    // 手持图片
                    if (!empty($config['certificate']['hold'])) {
                        $file = $req->file('hold');
                        if (empty($file)) {
                            return json([
                                'code'      =>  514,
                                'message'   =>  '很抱歉、请提供手持身份证图片！',
                            ]);
                        } else {
                            // 图片检查
                            $info = $file->validate(['ext' => 'jpg,jpeg,png'])->check();
                            if (!$info) {
                                return json([
                                    'code'      =>  515,
                                    'message'   =>  '很抱歉、错误的手持身份证图片！'
                                ]);
                            }
                            // 图片压缩
                            $image = \think\Image::open($file);
                            $certificate['hold'] = '/' . date('Ymd') . '/' . md5(time() . mt_rand()) . '.' . $image->type();
                            $image->thumb(640, 1136)->save($folder . $certificate['hold']);
                        }
                    }
                    // 资料格式化
                    $certificate = json_encode($certificate);
                }
                // 开启事务
                Db::startTrans();
                // 档案资料
                $data = [
                    'realname'  =>  $req->param('realname'),
                    'idcard'    =>  $req->param('idcard'),
                    'nickname'  =>  $req->param('nickname'),
                ];
                if (array_key_exists('certificate', $config)) {
                    $data['certificate'] = $certificate;
                }
                $this->attrs($username, $data);
                // 自动审核
                $audit = array_key_exists('audit', $config) ? $config['audit'] : true;
                if ($audit === false) {
                    // 用户状态
                    $this->update($username, [
                        'authen'    =>  1,
                    ]);
                    // 认证升级
                    $this->upgrade($username, 1);
                } else {
                    // 用户状态
                    $this->update($username, [
                        'authen'    =>  2,
                    ]);
                }
                // 保存日志
                $this->log(8);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json([
                    'code'      =>  504,
                    'message'   =>  $e->getMessage()
                ]);
            }
            // 返回结果
            return json([
                'code'          =>  200,
                'message'       =>  '恭喜您、操作成功！',
            ]);
        }
        // 注册需要审核
        $register_audit = config('hello.register_audit');
        if (!empty($register_audit)) {
            $audit_status = Db::table('account_audit')->where('username', '=', $username)->value('status');
            if (!is_null($audit_status) && $audit_status != 1) {
                $this->assign('audit', $this->timestamp);
            }
        }
        // 显示页面
        return $this->fetch();
    }

    /**
     * 注册账户
     */
    public function signup(Request $req)
    {
        if ($req->isPost()) {
            // 验证参数
            $rule = [
                'username'      =>  'require|mobile',
                'password'      =>  'require|length:6,32',
                'safeword'      =>  'require|length:6,32',
                'inviter'       =>  'require|length:6,11',
                'verify_code'   =>  'require|number|length:' . config('hello.sms.length'),
            ];
            if (empty(config('hello.inviter.enable'))) {
                $rule['inviter'] = 'length:6,11';
            }
            $msg  = [
                'username'      =>  '很抱歉、用户账号必须是正确的手机号码！',
                'password'      =>  '很抱歉、登录密码长度必须在6-32之间！',
                'safeword'      =>  '很抱歉、安全密码长度必须在6-32之间！',
                'inviter'       =>  '很抱歉、请填写正确的邀请码！',
                'verify_code'   =>  '很抱歉、短信验证码格式错误！',
            ];
            $validate = Validate::make($rule, $msg);
            if (!$validate->check($req->param())) {
                return json([
                    'code'      =>  501,
                    'message'   =>  $validate->getError(),
                ]);
            }
            // 检测短信
            $username = $req->param('username');
            $verify_code = $req->param('verify_code');
            $Service = new Service();
            if (!$Service->smsCheck($username, $verify_code)) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、短信验证码不正确！',
                ]);
            }
            // 获取Oauth
            $o = $req->param('o') ?: null;
            // 已开启邀请制
            $inviter = $req->param('inviter');
            // 需要邀请码
            if (!empty(config('hello.inviter.enable')) && empty($inviter)) {
                return json([
                    'code'      =>  503,
                    'message'   =>  '很抱歉、请提供邀请码！',
                ]);
            }
            // 存在邀请码
            if (!empty($inviter) && strlen($inviter) == 11) {
                $uid = $this->value($inviter, 'uid');
                if (empty($uid)) {
                    return json([
                        'code'      =>  503,
                        'message'   =>  '很抱歉、该邀请码不存在！',
                    ]);
                }
            }
            // 已开启邀请人匿名制
            if (!empty(config('hello.inviter.anonymous')) && !empty($inviter) && strlen($inviter) != 11) {
                // 根据UID查询账号
                $inviter = $this->uid_to_username($inviter);
                if (empty($inviter)) {
                    return json([
                        'code'      =>  503,
                        'message'   =>  '很抱歉、该邀请码不存在！',
                    ]);
                }
            }

            try {
                // 开启事务
                Db::startTrans();
                // 创建账户
                $user = $this->create($req->param('username'), $req->param('password'), $req->param('safeword'), $inviter, $o);
                // 登录账户
                $user = $this->login(null, null, $user);
                // 保存日志
                $this->log(1, $inviter ? '来自于推荐人' . $inviter : null);
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
                'code'          =>  200,
                'message'       =>  '恭喜您、注册成功！',
                'data'          =>  $user,
            ]);
        }
        return $this->fetch();
    }

    /**
     * 账号检查
     */
    public function check(Request $req)
    {
        $rule = [
            'mobile'      =>  'require|length:6,11',
        ];
        $msg  = [
            'mobile'      =>  '很抱歉、请输入正确的邀请码！',
        ];
        $validate = Validate::make($rule, $msg);
        if (!$validate->check($req->param())) {
            return json([
                'code'      =>  501,
                'message'   =>  $validate->getError(),
            ]);
        }
        $mobile = $req->param('mobile');
        if (strlen($mobile) == 11) {
            $bool = $this->exists($mobile);
        } else {
            $bool = $this->exists($mobile, true);
        }
        return json([
            'code'          =>  200,
            'message'       =>  '恭喜您、操作成功！',
            'data'          =>  [
                'exists'    =>  $bool,
            ]
        ]);
    }

    /**
     * 登录账户
     */
    public function signin(Request $req)
    {
        if ($req->isPost()) {
            // 验证参数
            $rule = [
                'username'      =>  'require|mobile',
                'password'      =>  'require|length:6,32',
            ];
            $msg  = [
                'username'      =>  '很抱歉、用户账号必须是正确的手机号码！',
                'password'      =>  '很抱歉、登录密码长度必须在6-32之间！',
            ];
            $validate = Validate::make($rule, $msg);
            if (!$validate->check($req->param())) {
                return json([
                    'code'      =>  501,
                    'message'   =>  $validate->getError(),
                ]);
            }
            // 登录账户
            $user = $this->login($req->param('username'), $req->param('password'));
            // 保存日志
            $this->log(3);
            // 返回结果
            return json([
                'code'          =>  200,
                'message'       =>  '恭喜您、登录成功！',
                'data'          =>  $user,
            ]);
        }
        return $this->fetch();
    }

    /**
     * 找回密码
     */
    public function forgot(Request $req)
    {
        if ($req->isPost()) {
            // 验证参数
            $rule = [
                'username'      =>  'require|mobile',
                'password'      =>  'require|length:6,32',
                'verify_code'   =>  'require|number|length:' . config('hello.sms.length'),
            ];
            $msg  = [
                'username'      =>  '很抱歉、用户账号必须是正确的手机号码！',
                'password'      =>  '很抱歉、登录密码长度必须在6-32之间！',
                'verify_code'   =>  '很抱歉、短信验证码格式错误！',
            ];
            $validate = Validate::make($rule, $msg);
            if (!$validate->check($req->param())) {
                return json([
                    'code'      =>  501,
                    'message'   =>  $validate->getError(),
                ]);
            }
            // 检测短信
            $username = $req->param('username');
            $verify_code = $req->param('verify_code');
            $Service = new Service();
            if (!$Service->smsCheck($username, $verify_code)) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、短信验证码不正确！',
                ]);
            }
            // 修改账户
            $this->update($username, ['password' => $req->param('password')]);
            // 保存日志
            $this->log(4, null, $username);
            // 退出登录
            $this->quit();
            // 返回结果
            return json([
                'code'          =>  200,
                'message'       =>  '恭喜您、操作成功！',
            ]);
        }
        return $this->fetch();
    }

    /**
     * 退出登录
     */
    public function signout()
    {
        $this->log(9);
        $this->quit();
        return redirect('/signin.html');
    }
}
