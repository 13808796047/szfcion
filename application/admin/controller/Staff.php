<?php

namespace app\admin\controller;

use think\Request;
use think\Db;

class Staff extends Base
{

    /**
     * 员工列表
     */
    public function index(Request $req)
    {
        // 查看员工
        $accounts = Db::table('staff_account')->select();
        $this->assign('accounts', $accounts);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 添加员工
     */
    public function create(Request $req)
    {
        // 获取账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供账号！');
            exit;
        }
        // 查询账号
        $ac = Db::table('staff_account')->where('username', '=', $username)->find();
        if (!empty($ac)) {
            $this->error('很抱歉、该账号已经存在！');
            exit;
        }
        // 获取密码
        $password = $req->param('password');
        if (empty($password)) {
            $this->error('很抱歉、请提供密码！');
            exit;
        }
        // 添加数据
        $bool = Db::table('staff_account')->insert([
            'status'        =>  1,
            'username'      =>  $username,
            'password'      =>  encryption($password),
            'create_at'     =>  $this->timestamp,
            'update_at'     =>  $this->timestamp,
        ]);
        if (empty($bool)) {
            $this->error('很抱歉、添加失败请重试！');
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 修改员工
     */
    public function update(Request $req)
    {
        // 获取编号
        $id = $req->param('id');
        if (empty($id)) {
            $this->error('很抱歉、请提供编号！');
            exit;
        }
        // 获取账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供账号！');
            exit;
        }
        // 查询账号
        $ac = Db::table('staff_account')->where('username', '=', $username)->where('id', '<>', $id)->find();
        if (!empty($ac)) {
            $this->error('很抱歉、该账号已经存在！');
            exit;
        }
        // 获取密码
        $password = $req->param('password');
        if (empty($password)) {
            $this->error('很抱歉、请提供密码！');
            exit;
        }
        // 修改数据
        $bool = Db::table('staff_account')->where('id', '=', $id)->update([
            'username'      =>  $username,
            'password'      =>  encryption($password),
            'create_at'     =>  $this->timestamp,
            'update_at'     =>  $this->timestamp,
        ]);
        if (empty($bool)) {
            $this->error('很抱歉、修改失败请重试！');
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 冻结员工
     */
    public function frozen(Request $req)
    {
        // 获取编号
        $id = $req->param('id');
        if (empty($id)) {
            $this->error('很抱歉、请提供编号！');
            exit;
        }
        // 查询账号
        $ac = Db::table('staff_account')->where('id', '=', $id)->find();
        if (empty($ac)) {
            $this->error('很抱歉、该账号不存在！');
            exit;
        }
        // 修改数据
        $bool = Db::table('staff_account')->where('id', '=', $id)->update([
            'status'      =>  0,
            'update_at'   =>  $this->timestamp,
        ]);
        if (empty($bool)) {
            $this->error('很抱歉、修改失败请重试！');
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 员工解冻
     */
    public function unfrozen(Request $req)
    {
        // 获取编号
        $id = $req->param('id');
        if (empty($id)) {
            $this->error('很抱歉、请提供编号！');
            exit;
        }
        // 查询账号
        $sa = Db::table('staff_account')->where('id', '=', $id)->find();
        if (empty($sa)) {
            $this->error('很抱歉、该账号不存在！');
            exit;
        }
        // 修改数据
        $bool = Db::table('staff_account')->where('id', '=', $id)->update([
            'status'      =>  1,
            'update_at'   =>  $this->timestamp,
        ]);
        if (empty($bool)) {
            $this->error('很抱歉、修改失败请重试！');
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 节点列表
     */
    public function role(Request $req)
    {
        // 查询数据
        $roles = Db::table('staff_role')->order('id ASC')->select();
        $roles = $this->roles($roles);
        $this->assign('roles', $roles);
        // 显示页面
        return $this->fetch();
    }


    /**
     * 权限列表
     */
    public function power(Request $req)
    {
        // 用户编号
        $id = $req->param('id/d');
        if (empty($id)) {
            $this->error('很抱歉、请提供管理员编号！');
            exit;
        }
        // 保存权限
        if ($req->isPost()) {
            // 获取节点
            $roles = $req->param('roles/a', []);
            try {
                // 开启事务
                Db::startTrans();
                // 先删除所有节点
                Db::table('staff_power')->where('staff', '=', $id)->delete();
                // 再添加这一批节点
                $data = [];
                foreach ($roles as $key => $value) {
                    $data[] = [
                        'staff'     =>  $id,
                        'role'      =>  $value,
                        'create_at' =>  $this->timestamp,
                    ];
                }
                // 添加数据
                if (!empty($data)) {
                    Db::table('staff_power')->insertAll($data);
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
        }
        // 所有节点
        $roles = Db::table('staff_role')->order('id ASC')->select();
        // 我的节点
        $myRole = Db::table('staff_power')->where('staff', '=', $id)->order('id ASC')->column('role');
        $roles = $this->roles($roles, $myRole);
        $this->assign('roles', $roles);
        // 我的节点
        // 显示页面
        return $this->fetch();
    }



    /**
     * 员工登录
     */
    public function login(Request $req)
    {
        // 执行登录
        if ($req->isPost()) {
            // 获取账号
            $username = $req->param('username');
            // 获取密码
            $password = $req->param('password');
            // 对比账号
            if ($username == config('hello.admin.username') && $password == config('hello.admin.password')) {
                // 所有节点
                $roles = Db::table('staff_role')->order('id ASC')->select();
                // 登录成功
                session('roles', $roles);
                session('staff', time());
                session('login_at', time());
                $this->redirect('/admin.html');
                exit;
            } else {
                // 查询账号
                $sa = Db::table('staff_account')->where('username', '=', $username)->find();
                if (empty($sa) || $sa['password'] != encryption($password)) {
                    $this->error('很抱歉、账号或密码错误！');
                    exit;
                }
                // 是否停用
                if (empty($sa['status'])) {
                    $this->error('很抱歉、您的账号已被停用！');
                    exit;
                }
                // 查询权限
                $power = Db::table('staff_power')->alias('sp')->leftJoin('staff_role sr', 'sr.id = sp.role')->field('sr.*')->where('staff', '=', $sa['id'])->order('id ASC')->select();
                if (empty($power)) {
                    $this->error('很抱歉、您没有任何权限！');
                    exit;
                }
                // 所有节点
                $roles = Db::table('staff_role')->order('id ASC')->select();
                // 我的节点
                $myRole = Db::table('staff_power')->where('staff', '=', $sa['id'])->order('id ASC')->column('role');
                $roles = $this->roles($roles, $myRole);
                $array = [];
                $array2 = [];
                foreach ($roles as $key => $item) {
                    if (!empty($item['checked'])) {
                        $array[] = $item['id'];
                        if (!empty($item['path'])) {
                            $array2[] = $item['path'];
                        }
                    }
                }
                // 修改资料
                Db::table('staff_account')->where('id', '=', $sa['id'])->update([
                    'ip'        =>  $req->ip(),
                    'ua'        =>  $req->header('user-agent'),
                    'login_at'  =>  $this->timestamp,
                ]);
                // 保存资料
                session('staff', null);
                session('login_at', time());
                session('manager', $sa);
                $this->redirect($array2[0] . '.html');
                exit;
            }
        }
        // 显示页面
        return $this->fetch();
    }

    /**
     * 操作日志
     */
    public function logs(Request $req)
    {
        // 查询日志
        $query = Db::table('log');
        // 按账号搜索
        $username = $req->param('username');
        if (!empty($username)) {
            $query->where('username', '=', $username);
        }
        // 按类型搜索
        $type = $req->param('type');
        if (!empty($type)) {
            $query->where('type', '=', $type);
        }
        // 按IP搜索
        $ip = $req->param('ip');
        if (!empty($ip)) {
            $query->where('ip', '=', $ip);
        }
        // 按描述搜索
        $text = $req->param('text');
        if (!empty($text)) {
            $query->where('text', 'like', "%$text%");
        }
        // 按时间搜索
        $date = $req->param('date');
        if (!empty($date)) {
            $query->where('create_at', 'like', "%$date%");
        }
        // 搜索数据
        $logs = $query->order('create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('logs', $logs);
        // 显示页面
        return $this->fetch();
    }
}
