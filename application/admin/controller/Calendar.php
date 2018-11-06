<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\facade\Env;
use app\index\controller\Configure;

class Calendar extends Base
{
    /**
     * 老板首页
     */
    public function index(Request $req)
    {
        // 配置内容
        $config = Configure::get('hello.calendar');
        $config = empty($config) ? [] : $config;
        $this->assign('config', $config);
        // 查询对象
        $query = Db::table('calendar_log');
        // 条件：按账号搜索
        $username = $req->param('username');
        if (!is_null($username) && strlen($username)) {
            $query->where('username', 'like', "%$username%");
        }
        // 查询老板
        $users = $query->order('create_at DESC')->paginate(20, false, ['query' => $req->param()]);
        $this->assign('users', $users);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 配置保存
     */
    public function config(Request $req)
    {
        // 获取配置
        $config = Configure::get('hello.calendar');
        $config = empty($config) ? [] : $config;
        // 获取参数
        $param = $req->param();
        $param['enable'] = empty($param['enable']) ? false : true;
        // 合并参数
        $config = array_merge($config, $param);
        // 保存设置
        try {
            Configure::set('hello.calendar', $config);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }
}
