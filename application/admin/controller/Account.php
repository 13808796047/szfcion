<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\facade\Env;

class Account extends Base
{
    /**
     * 用户列表
     */
    public function index(Request $req)
    {
        // 资金字段
        $currencys = config('hello.currencys');
        $fields = [];
        foreach ($currencys as $key => $item) {
            $fields['w.' . $item['field']] = $item['name'];
        }
        $fields['a.create_at'] = '注册时间';
        $this->assign('fields', $fields);
        // 查询对象
        $query = Db::table('account')->alias('a')
                    ->join('profile p', 'p.username = a.username')
                    ->join('wallet w', 'w.username = a.username')
                    // ->join('dashboard d', 'd.username = a.username')
                    ->field('p.avatar, p.idcard, p.nickname, a.create_at, a.username, a.type, a.status, a.authen, a.inviter, w.rmb, w.money, w.deposit, w.score, w.score_deposit, w.release');
        // 条件：按类型搜索
        $type = $req->param('type');
        if (!is_null($type) && $type != -1) {
            $query->where('a.type', '=', $type);
        }
        // 条件：按状态搜索
        $status = $req->param('status');
        if (!is_null($status) && $status != -1) {
            $query->where('a.status', '=', $status);
        }
        // 条件：按实名搜索
        $authen = $req->param('authen');
        if (!is_null($authen) && $authen != -1) {
            $query->where('a.authen', '=', $authen);
        }
        // 条件：按数值搜索
        $numberField = $req->param('numberField');
        $numberOperator = $req->param('numberOperator');
        $numberValue = $req->param('numberValue');
        if (!is_null($numberField) && !is_null($numberOperator) && !is_null($numberValue) && strlen($numberValue)) {
            if ($numberOperator == 1) {
                $query->where($numberField, '>', $numberValue);
            } else if ($numberOperator == 2) {
                $query->where($numberField, '=', $numberValue);
            } else if ($numberOperator == 3) {
                $query->where($numberField, '<', $numberValue);
            }
        }
        // 排序
        $sortField = $req->param('sortField');
        $sortType = $req->param('sortType');
        if (!is_null($sortField)) {
            $query->order($sortField . ' ' . $sortType);
        } else {
            $query->order('w.rmb DESC, w.money DESC');
        }
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('a.username', 'like', "%$username%");
        }
        // 条件：按上级搜索
        $inviter = $req->param('inviter');
        if (!is_null($inviter) && strlen($inviter) && $inviter != 'null') {
            $query->where('a.inviter', 'like', "%$inviter%");
        } else if (!is_null($inviter) && $inviter == 'null') {
            $query->where('a.inviter', 'null');
        }
        // 搜索数据
        $users = $query->paginate(20, false, ['query' => $req->param()]);
        $this->assign('users', $users);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 用户档案
     */
    public function profile(Request $req)
    {
        // 查询对象
        $query = Db::table('profile')->alias('p');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('p.username', 'like', "%$username%");
        }
        // 排序
        $query->order('p.update_at DESC');
        // 搜索数据
        $users = $query->paginate(20, false, ['query' => $req->param()]);
        // echo Db::getLastSql();
        $this->assign('users', $users);
        return $this->fetch();
    }

    /**
     * 仪表盘
     */
    public function dashboard(Request $req)
    {
        // 查询对象
        $query = Db::table('dashboard')->alias('d')
                    ->join('profile p', 'p.username = d.username')
                    ->field('p.avatar, p.idcard, p.nickname, d.*');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('d.username', 'like', "%$username%");
        }
        // 条件：按数值搜索
        $numberField = $req->param('numberField');
        $numberOperator = $req->param('numberOperator');
        $numberValue = $req->param('numberValue');
        if (!is_null($numberField) && !is_null($numberOperator) && !is_null($numberValue) && strlen($numberValue)) {
            if ($numberOperator == 1) {
                $query->where('d.'.$numberField, '>', $numberValue);
            } else if ($numberOperator == 2) {
                $query->where('d.'.$numberField, '=', $numberValue);
            } else if ($numberOperator == 3) {
                $query->where('d.'.$numberField, '<', $numberValue);
            }
        }
        // 排序
        $sortField = $req->param('sortField');
        $sortType = $req->param('sortType');
        if (!is_null($sortField)) {
            $query->order('d.' . $sortField . ' ' . $sortType);
        } else {
            $query->order('d.power DESC');
        }
        // 搜索数据
        $users = $query->paginate(20, false, ['query' => $req->param()]);
        // echo Db::getLastSql();
        $this->assign('users', $users);
        $this->assign('fields', [
            'power'         =>  '总算力',
            'team_power'    =>  '团队算力',
            'machine_power' =>  '矿机算力',
            'team_count'    =>  '团队人数',
            'machine_count' =>  '矿机数量',
            'machine_expire'=>  '过期矿机',
        ]);
        return $this->fetch();
    }

    /**
     * 用户推广
     */
    public function promotion(Request $req)
    {
        // 查询对象
        $query = Db::table('dashboard')->alias('d')
                    ->join('profile p', 'p.username = d.username')
                    ->field('p.avatar, p.idcard, p.nickname, d.*');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('d.username', 'like', "%$username%");
        }
        // 条件：按数值搜索
        $numberField = $req->param('numberField');
        $numberOperator = $req->param('numberOperator');
        $numberValue = $req->param('numberValue');
        if (!is_null($numberField) && !is_null($numberOperator) && !is_null($numberValue) && strlen($numberValue)) {
            if ($numberOperator == 1) {
                $query->where('d.'.$numberField, '>', $numberValue);
            } else if ($numberOperator == 2) {
                $query->where('d.'.$numberField, '=', $numberValue);
            } else if ($numberOperator == 3) {
                $query->where('d.'.$numberField, '<', $numberValue);
            }
        }
        // 排序
        $sortField = $req->param('sortField');
        $sortType = $req->param('sortType');
        if (!is_null($sortField)) {
            $query->order('d.' . $sortField . ' ' . $sortType);
        } else {
            $query->order('d.one DESC');
        }
        // 搜索数据
        $users = $query->paginate(20, false, ['query' => $req->param()]);
        // echo Db::getLastSql();
        $this->assign('users', $users);
        $this->assign('fields', [
            'one'       =>  '一代',
            'two'       =>  '二代',
            'three'     =>  '三代',
            'four'      =>  '四代',
            'five'      =>  '五代',
            'six'       =>  '六代',
            'seven'     =>  '七代',
            'eight'     =>  '八代',
            'lv0'       =>  'Lv0',
            'lv1'       =>  'Lv1',
            'lv2'       =>  'Lv2',
            'lv3'       =>  'Lv3',
            'lv4'       =>  'Lv4',
            'lv5'       =>  'Lv5',
            'lv6'       =>  'Lv6',
            'lv7'       =>  'Lv7',
            'lv8'       =>  'Lv8',
        ]);
        return $this->fetch();
    }

    /**
     * 审核列表
     */
    public function audit(Request $req)
    {
        // 查询对象
        $query = Db::table('account')->alias('a')
                    ->join('profile p', 'p.username = a.username')
                    ->field('p.avatar, p.idcard, p.nickname, p.realname, p.idcard, p.certificate, p.authen_reason, a.create_at, a.username, a.type, a.status, a.authen');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!empty($username)) {
            $query->where('a.username', '=', $username);
        }
        // 查询数据
        $users = $query->where('authen', '=', 2)
                    ->order('a.update_at DESC')->paginate(20)->each(function($item){
                        if (!empty($item['certificate'])) {
                            $item['certificate'] = json_decode($item['certificate'], true);
                            if (!array_key_exists('front', $item['certificate'])) {
                                $item['certificate']['front'] = null;
                            }
                            if (!array_key_exists('back', $item['certificate'])) {
                                $item['certificate']['back'] = null;
                            }
                            if (!array_key_exists('hold', $item['certificate'])) {
                                $item['certificate']['hold'] = null;
                            }
                        }
                        return $item;
                    });
        $this->assign('users', $users);
        return $this->fetch();
    }

    /**
     * 注册审核
     */
    public function reg_audit(Request $req)
    {
        // 进行审核
        $action = $req->param('action');
        if (!empty($action)) {
            // 获取账号
            $username = $req->param('username');
            // 修改状态
            $status = $action == 'ok' ? 1: 0;
            // 修改数据
            $bool = Db::table('account_audit')->where('username', '=', $username)->update([
                'status'      =>  $status,
                'update_at'   =>  $this->timestamp,
            ]);
            if (empty($bool)) {
                $this->error('很抱歉、操作失败请重试！');
                exit;
            }
            $this->success('恭喜您、操作成功！');
            exit;
        }
        // 查询对象
        $query = Db::table('account_audit');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', 'like', "%$username%");
        }
        // 排序
        $query->order('create_at DESC');
        // 搜索数据
        $users = $query->paginate(20, false, ['query' => $req->param()]);
        $this->assign('users', $users);
        return $this->fetch();
    }

    /**
     * 创建账号
     */
    public function create(Request $req)
    {
        $level = config('hello.level');
        if ($req->isPost()) {
            try {
                // 开启事务
                Db::startTrans();
                // 账户对象
                $AccountController = new \app\index\controller\Account();
                // 用户账号
                $username = $req->param('username');
                // 创建账户
                $user = $AccountController->create(
                    $username,
                    $req->param('password'),
                    $req->param('safeword'),
                    $req->param('inviter') ?: null
                );
                // 发送级别奖励
                $type = $req->param('type');
                if (!empty($type) && $type > 0) {
                    for ($i = 1;$i <= $type; $i++) {
                        $AccountController->upgrade($username, $i, true);
                    }
                }
                // 记录日志
                $this->log(2, $username);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
                exit;
            }
            $this->success('恭喜您、账号添加成功！');
            exit;
        }
        $this->assign('level', $level);
        return $this->fetch();
    }

    /**
     * 编辑用户
     */
    public function edit(Request $req)
    {
        // 用户编号
    	$username = $req->param('username');
    	if (empty($username)) {
    		$this->error('很抱歉、请提供用户编号！');
    		exit;
    	}
        // 账户对象
        $ac = new \app\index\controller\Account();
        // 查询资料
        $user = $ac->instance($username);
        if (empty($user)) {
            $this->error('很抱歉、该用户不存在！');
            exit;
        }
        // 编辑档案
        if ($req->isPost()) {
            try {
                // 开启事务
                Db::startTrans();
                // 获取参数
                $data = $req->param();
                // 更新头像
                $avatarFile = $req->file('avatar');
                if (!empty($avatarFile)) {
                    $info = $avatarFile->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/avatar');
                    if (!$info) {
                        $this->error($avatarFile->getError());
                        exit;
                    } else {
                        $data['avatar'] = $info->getSaveName();
                    }
                }
                // 对比差异
                $acData = array_diff_assoc($data, $user['profile']);
                // 存在差异
                if (!empty($acData)) {
                    // 更新档案
                    $ac->attrs($username, $acData);
                    // 记录日志
                    $this->log(10, $username, json_encode($acData));
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('恭喜您、操作成功！');
            exit;
        }
        // 用户日志
        $logs = Db::table('log')->where('username', '=', $username)->order('id DESC')->paginate(20, false, [
            'query' =>  $req->param()
        ]);
        $this->assign('logs', $logs);
        $this->assign('user', $user);
        return $this->fetch();
    }

    /**
     * 调整级别
     */
    public function level(Request $req)
    {
        try {
            // 用户账号
            $username = $req->param('username');
            if (empty($username)) {
                $this->error('很抱歉、请提供用户编号！');
                exit;
            }
            // 开启事务
            Db::startTrans();
            // 账户对象
            $ac = new \app\index\controller\Account();
            // 查询账号
            $user = $ac->instance($username);
            if (empty($user)) {
                $this->error('很抱歉、该用户不存在！');
                exit;
            }
            // 用户升级
            $type = $req->param('type/d', 0);
            for ($i = 0;$i <= $type; $i++) {
                $ac->upgrade($username, $i, true);
            }
            // 记录日志
            $this->log(14, $username);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
            exit;
        }
        $this->success('恭喜您、操作成功！');
    }

    /**
     * 调整状态
     */
    public function status(Request $req)
    {
        try {
            // 用户账号
            $username = $req->param('username');
            if (empty($username)) {
                $this->error('很抱歉、请提供用户编号！');
                exit;
            }
            // 开启事务
            Db::startTrans();
            // 账户对象
            $ac = new \app\index\controller\Account();
            // 查询账号
            $user = $ac->instance($username);
            if (empty($user)) {
                $this->error('很抱歉、该用户不存在！');
                exit;
            }
            // 更新账号
            $ac->update($username, ['status' => $req->param('status/d')], false);
            // 记录日志
            $this->log(15, $username);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
            exit;
        }
        $this->success('恭喜您、操作成功！');
    }

    /**
     * 资金调整
     */
    public function capital(Request $req)
    {
        // 用户账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供用户编号！');
            exit;
        }
        try {
            // 开启事务
            Db::startTrans();
            // 查询账号
            $user = (new \app\index\controller\Account())->instance($username);
            if (empty($user)) {
                $this->error('很抱歉、该用户不存在！');
                exit;
            }
            // 流水记录
            $record = [];
            $logs = [];
            // 货币类型
            $currencys = config('hello.currencys');
            foreach ($currencys as $key => $item) {
                // 具体字段
                $field = $item['field'];
                // 更改过了
                $number = $req->param($field . '/f');
                if ($number != $user['wallet'][$field]) {
                    // 流水记录
                    $record[$key] = [
                        $user['wallet'][$field],
                        $number - $user['wallet'][$field],
                        $number,
                    ];
                    // 日志记录
                    $logs[] = '原' . $item['name'] . '：' . $user['wallet'][$field] . '，现' . $item['name'] . '：' . $number;
                }
            }
            if (!empty($record)) {
                // 更新钱包
                (new \app\index\controller\Wallet())->change($username, 88, $record);
                // 日志记录
                $this->log(11, $username, implode(',', $logs));
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            echo Db::getLastSql();
            $this->error($e->getMessage());
        }
        // 操作成功
        $this->success('恭喜您、用户资金调整成功！');
    }

    /**
     * 密码修改
     */
    public function password(Request $req)
    {
        // 用户账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供用户编号！');
            exit;
        }
        try {
            // 开启事务
            Db::startTrans();
            // 账户对象
            $ac = new \app\index\controller\Account();
            // 查询账号
            $user = $ac->instance($username);
            if (empty($user)) {
                $this->error('很抱歉、该用户不存在！');
                exit;
            }
            // 修改数据
            $data = [];
            $text = '';
            $password = $req->param('password');
            if (!empty($password)) {
                $data['password'] = $password;
                $text .= '登录密码：' . $password;
            }
            $safeword = $req->param('safeword');
            if (!empty($safeword)) {
                $data['safeword'] = $safeword;
                if ($text) {
                    $text .= '，';
                }
                $text .= '安全密码：' . $safeword;
            }
            if (!empty($data)) {
                // 更新账户
                $ac->update($user['account']['username'], $data, false);
                // 系统日志
                $this->log(13, $username, $text);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        // 操作成功
        $this->success('恭喜您、用户密码修改成功！');
    }

    /**
     * 实名认证
     */
    public function authen(Request $req)
    {
        // 用户账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供用户编号！');
            exit;
        }
        try {
            // 开启事务
            Db::startTrans();
            // 账户对象
            $ac = new \app\index\controller\Account();
            // 查询账号
            $user = $ac->instance($username);
            if (empty($user)) {
                $this->error('很抱歉、该用户不存在！');
                exit;
            }
            // 具体数据
            $data = [];
            // 获取姓名
            $realname = $req->param('realname');
            if (!empty($realname)) {
                $data['realname'] = $realname;
            }
            // 获取身份证
            $idcard = $req->param('idcard');
            if (!empty($idcard)) {
                $data['idcard'] = $idcard;
            }
            // 获取状态
            $authen = $req->param('authen');
            // 获取理由
            $authen_reason = $req->param('authen_reason');
            if (!empty($authen_reason)) {
                $data['authen_reason'] = $authen_reason;
            }
            // 用户更新
            $ac->update($username, ['authen' => $authen]);
            // 实名调整
            $ac->attrs($username, $data);
            // 认证升级
            if ($authen == 1 && $user['account']['type'] == 0) {
                $ac->upgrade($username, 1);
            }
            // 系统日志
            $this->log(12, $username);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        // 操作成功
        $this->success('恭喜您、实名认证调整成功！');
    }

    /**
     * 快速实名认证
     */
    public function quickAuthen(Request $req)
    {
        // 用户账号
        $username = $req->param('username');
        if (empty($username)) {
            return json([
                'code'      =>  501,
                'message'   =>  '很抱歉、请提供用户编号！'
            ]);
        }
        try {
            // 开启事务
            Db::startTrans();
            // 账户对象
            $ac = new \app\index\controller\Account();
            // 查询账号
            $user = $ac->instance($username);
            if (empty($user)) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、该用户不存在！'
                ]);
            }
            // 获取状态
            $authen = $req->param('authen/d');
            if ($user['account']['authen'] == 1 && $user['account']['authen'] == $authen) {
                return json([
                    'code'      =>  503,
                    'message'   =>  '很抱歉、请勿重复通过认证！'
                ]);
            }
            // 原类型
            $oldType = $user['account']['type'];
            // 通过认证
            if ($authen == 1) {
                // 用户更新
                $ac->update($username, ['authen' => 1]);
                // 认证升级
                if (empty($oldType)) {
                    $ac->upgrade($username, 1);
                }
            } else {
                // 用户更新
                $ac->update($username, ['authen' => 3]);
                // 实名调整
                $ac->attrs($username, ['authen_reason' => $req->param('authen_reason')]);
            }
            // 系统日志
            $this->log(12, $username);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  510,
                'message'   =>  $e->getMessage(),
                'trace'     =>  $e->getTrace()
            ]);
        }
        // 操作成功
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、实名认证调整成功！'
        ]);
    }

    /**
     * 推荐人
     */
    public function inviter(Request $req)
    {
        // 我的账号
        $username = $req->param('username');
        // 账户对象
        $ac = new \app\index\controller\Account();
        // 查询账号
        $user = $ac->instance($username);
        if (empty($user)) {
            $this->error('很抱歉、该用户不存在！');
            exit;
        }
        // 获取推荐人
        $inviter = $req->param('inviter');
        // 原本推广人
        $oldInviter = $user['account']['inviter'];
        try {
            // 开启事务
            Db::startTrans();
            // 用户对象
            $ac = new \app\index\controller\Account();
            // 1. 修改自身推广人
            $ac->update($username, ['inviter' => $inviter]);
            // 2. 新的上级更新仪表盘、人数增加
            if (!empty($inviter)) {
                $ac->upgrade($inviter);
                $ac->dashboard_inviter($inviter, $user['dashboard'], $user['account']['type'], '+');
            }
            // 3. 原所有上级Lv减少、代数减少
            if (!empty($oldInviter)) {
                $ac->dashboard_inviter($oldInviter, $user['dashboard'], $user['account']['type'], '-');
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
            exit;
        }
        // 返回结果
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 模拟登录
     */
    public function simulate(Request $req)
    {
        // 用户账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供用户编号！');
            exit;
        }
        // 账户对象
        $ac = new \app\index\controller\Account();
        // 查询账号
        $user = $ac->instance($username);
        if (empty($user)) {
            $this->error('很抱歉、该用户不存在！');
            exit;
        }
        // 保存会话
        session('user', $user);
        // 要去的地址
        $to = $req->param('to');
        $to = empty($to) ? '/account.html' : urldecode($to);
        // 跳转到前台
        $this->redirect($to);
        exit;
    }

}
