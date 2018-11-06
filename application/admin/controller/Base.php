<?php

namespace app\admin\controller;

use think\Db;
use think\Controller;

class Base extends Controller
{

	public $timestamp;

	public function __construct()
	{
		parent::__construct();
		$this->timestamp = date('Y-m-d H:i:s');
	}

	/**
     * 节点处理
     */
    public function roles($array, $myRole = [], $parent = 0, $index = 1)
    {
        // 循环节点
        foreach ($array as $key => $item) {
            // 找到了
            if ($item['parent'] == $parent) {
                // 保存层级
                $item['index'] = $index;
                // 我是否拥有权限
                $item['checked'] = in_array($item['id'], $myRole);
                // 链接小写
                $item['path'] = strtolower($item['path']);
                // 继续寻找
                $array = $this->roles($array, $myRole, $item['id'], $index + 1);
                // 保存数据
                $array[$key] = $item;
            }
        }
        // 返回节点
        return $array;
    }

	public function initialize()
	{
		// 请求对象
		$req = request();
		// 无需登录的方法
		$except = ['/admin/Staff/login'];
		// 当前路由
		$current = '/' . $req->module() . '/' . $req->controller() . '/' . $req->action();
		// 隐秘入口
		$enter = config('hello.admin.enter');
		if (!empty($enter) && !session('?staff') && !session('?manager') && $req->path() != $enter) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		// 无需登录的方法
		if (!in_array($current, $except)) {

			// 员工编号
			$sid = 0;

			// 没有Session、需要进行检测
			if (!session('?staff') && !session('?manager')) {
				if (!empty($enter) && $req->path() != $enter) {
					header('HTTP/1.1 404 Not Found');
				} else {
					header('Location: /admin/login.html?from=' . urlencode($req->url(true)));
				}
				exit;
			}
			// 登录超时
			if (!session('?login_at') || time() - session('login_at') > 3600) {
				if (!empty($enter) && $req->path() != $enter) {
					header('HTTP/1.1 404 Not Found');
				} else {
					header('Location: /admin/login.html?from=' . urlencode($req->url(true)));
				}
				exit;
			}

			// 如果是超级管理员
			if (session('?staff')) {
				session('manager', null);
			}
			// 如果是员工管理员
			else if (session('?manager')) {
				// 获取账号
				$username = session('manager.username');

				// 查询账号
				$sa = Db::table('staff_account')->where('username', '=', $username)->find();
				if (empty($sa)) {
					session(null);
				    $this->error('很抱歉、该账号不存在！');
				    exit;
				}
				// 是否停用
				if (empty($sa['status'])) {
					session(null);
				    $this->error('很抱歉、您的账号已被停用！');
				    exit;
				}
				// 员工编号
				$sid = $sa['id'];
				// 查询权限
				$power = Db::table('staff_power')->alias('sp')->leftJoin('staff_role sr', 'sr.id = sp.role')->field('sr.*')->where('staff', '=', $sa['id'])->order('id ASC')->select();
				if (empty($power)) {
					session(null);
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

				// 保存数据
				session('roles', $roles);
				session('power_id', $array);
				session('power_path', $array2);

				// 获取权限
				$power_path = session('power_path');
				if (!in_array(strtolower($current), $power_path)) {
					$this->error('很抱歉、您没有权限执行该操作！', '/admin/login.html');
					exit;
				}
			}

			// 访问记录
			Db::table('staff_log')->insert([
				'staff'		=>	$sid,
				'path'		=>	$current,
				'method'	=>	$req->method(),
				'param'		=>	json_encode($req->param()),
				'ip'		=>	$req->ip(),
				'ua'		=>	$req->header('user-agent'),
				'create_at'	=>	date('Y-m-d H:i:s'),
			]);

		}
	}

	public function log($type, $username, $text = null)
	{
		$req = request();
		$data = [
			'username'	=>	$username,
			'type'		=>	$type,
			'text'		=>	$text,
			'ip'		=>	$req->ip(),
			'ua'		=>	$req->header('user-agent'),
			'create_at'	=>	$this->timestamp,
		];
		Db::table('log')->insert($data);
	}
}