<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\facade\Env;
use app\index\controller\Configure;

class Boss extends Base
{
    /**
     * 老板首页
     */
    public function index(Request $req)
    {
        // 配置内容
        $config = Configure::get('hello.boss');
        $config = empty($config) ? [] : $config;
        $this->assign('config', $config);
        // 查询对象
        $query = Db::table('boss');
        // 查询老板
        $users = $query->alias('b')
            ->field('p.realname, p.avatar, p.idcard, w.money, d.power, w.money * d.power AS weight, r.province_name, b.*')
            ->join('profile p', 'p.username = b.username')
            ->join('wallet w', 'w.username = b.username')
            ->join('dashboard d', 'd.username = b.username')
            ->join('region r', 'r.code = p.county')
            ->order('weight DESC, b.create_at DESC')
            ->paginate(20, false, ['query' => $req->param()]);
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
        $config = Configure::get('hello.boss');
        $config = empty($config) ? [] : $config;
        // 获取参数
        $param = $req->param();
        $param['enable'] = empty($param['enable']) ? false : true;
        // 合并参数
        $config = array_merge($config, $param);
        // 保存设置
        try {
            Configure::set('hello.boss', $config);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit;
        }
        // 操作成功
        $this->success('恭喜您、操作成功！');
        exit;
    }

    /**
     * 状态审核
     */
    public function audit(Request $req)
    {
        // 获取账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供账号！');
            exit;
        }
        // 获取状态
        $status = $req->param('status');
        $status = $status == 'yes' ? 1 : 3;
        // 修改状态
        $bool = Db::table('boss')->where('username', '=', $username)->update([
            'status'    =>  $status,
            'update_at' =>  $this->timestamp,
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
     * 编辑档案
     */
    public function edit(Request $req)
    {
        // 获取账号
        $username = $req->param('username');
        if (empty($username)) {
            $this->error('很抱歉、请提供账号！');
            exit;
        }
        // 查询资料
        $user = (new \app\index\controller\Account())->instance($username);
        if (empty($user)) {
            $this->error('很抱歉、该用户不存在！');
            exit;
        }
        // 提交更改
        if ($req->isPost()) {
            try {
                // 开启事务
                Db::startTrans();
                // 获取数据
                $data = ['update_at' => $this->timestamp];
                $data['realname'] = $req->param('realname');
                // $data['alipay'] = $req->param('alipay');
                $data['wechat'] = $req->param('wechat');
                $data['qq'] = $req->param('qq');
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
                // 修改资料
                $bool = Db::table('profile')->where('username', '=', $username)->update($data);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、用户资料更新失败！");
                }
                // 获取数据
                $data = ['update_at' => $this->timestamp];
                $data['status'] = $req->param('status');
                $data['title'] = $req->param('title');
                $data['advantage'] = $req->param('advantage');
                $data['background'] = $req->param('background');
                // 修改资料
                $bool = Db::table('boss')->where('username', '=', $username)->update($data);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、BOSS资料更新失败！");
                }
                // 记录日志
                $this->log(10, $username);
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
        // 查询资料
        $boss = Db::table('boss')->where('username', '=', $username)->find();
        $this->assign('boss', $boss);
        // 显示页面
        $this->assign('statuses', [
            0       =>  '隐藏',
            1       =>  '正常',
            2       =>  '审核',
            3       =>  '拒绝',
        ]);
        $this->assign('user', $user);
        return $this->fetch();
    }
}
