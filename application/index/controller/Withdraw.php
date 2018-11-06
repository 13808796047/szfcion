<?php

namespace app\index\controller;

use think\Db;
use think\Request;

class Withdraw extends Base
{

	// +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------

    /**
     * 生成WID
     */
    public function generateWID()
    {
        $prefix = chr(mt_rand(65, 90));
        do {
            $number = mt_rand(100000000, 999999999);
            $mid = $prefix . $number;
        } while (!empty(Db::table('uuid')->where('id', '=', $mid)->find()));
        $bool = Db::table('uuid')->insert(['id' => $mid, 'type' => 21]);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、提现编号生成失败！");
        }
        return $mid;
    }

    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

	/**
	 * 申请提现
	 */
	public function post(Request $req)
	{
		try {
			// 提现配置
			$config = Configure::get('hello.withdraw');
        	$config = empty($config) ? [] : $config;
			if (empty($config) || empty($config['enable'])) {
				throw new \think\Exception("很抱歉、系统尚未开启提现服务！");
			}
			// 开启事务
			Db::startTrans();
			// 用户账号
			$username = session('user.account.username');
			if (empty($username)) {
				throw new \think\Exception("很抱歉、请重新登录！");
			}
			// 安全密码
			$safeword = $req->param('safeword');
			if (empty($safeword)) {
				throw new \think\Exception("很抱歉、请提供安全密码！");
			}
			// 用户对象
			$user = (new Account())->instance($username, null, $safeword);
			if (empty($user)) {
				throw new \think\Exception("很抱歉、安全密码不正确！");
			}
			if (empty($user['account']['status'])) {
				throw new \think\Exception("很抱歉、您的账号已被冻结！");
			}
			if ($user['account']['authen'] != 1) {
				throw new \think\Exception("很抱歉、请先通过实名认证！");
			}
			// 提现金额
			$money = $req->param('money');
			if (empty($money) || $money < 0) {
				throw new \think\Exception("很抱歉、错误的提现金额！");
			}
			if ($money < $config['min'] || $money > $config['max']) {
				throw new \think\Exception("很抱歉、提现金额必须在" . $config['min'] . '-' . $config['max'] . '之间！');
			}
			// 到账方式
			$channel = $req->param('channel');
			if (empty($channel)) {
				throw new \think\Exception("很抱歉、您想提现到哪里！");
			}
			if (!in_array($channel, ['alipay', 'bank'])) {
				throw new \think\Exception("很抱歉、错误的提现方式！ -$channel");
			}
			// 资料
			$source = ['realname' => $user['profile']['realname']];
			// 提现到支付宝
			if ($channel == 'alipay') {
				if (empty($user['profile']['alipay'])) {
					throw new \think\Exception("很抱歉、请先完善个人资料，将支付宝等信息补充完整！");
				} else {
					$source['alipay'] = $user['profile']['alipay'];
				}
			}
			// 提现到网银
			if ($channel == 'bank') {
				if (empty($user['profile']['bankcard'])) {
					throw new \think\Exception("很抱歉、请先完善个人资料，将银行卡等信息补充完整！");
				} else {
					$source['bankname'] = $user['profile']['bankname'];
					$source['bankcard'] = $user['profile']['bankcard'];
				}
			}
			// 计算手续费
			$charge = 0;
			if (array_key_exists('charge', $config)) {
				$charge = $money * $config['charge'];
			}
			// 判断余额
			if ($user['wallet']['rmb'] < $money + $charge) {
				throw new \think\Exception("很抱歉、您的余额不足！");
			}
			// 添加记录
			$bool = Db::table('withdraw')->insert([
				'wid'		=>	$this->generateWID(),
				'status'	=>	2,
				'username'	=>	$username,
				'channel'	=>	$channel,
				'currency'	=>	8,
				'number'	=>	$money,
				'charge'	=>	$charge,
				'source'	=>	json_encode($source),
				'remark'	=>	null,
				'reason'	=>	null,
				'notify_at'	=>	null,
				'create_at'	=>	$this->timestamp,
				'update_at'	=>	$this->timestamp,
			]);
			if (empty($bool)) {
				throw new \think\Exception("很抱歉、提现订单生成失败！");
			}
			// 扣除金额
			(new Wallet())->change($username, 87, [
				8 	=>	[
					$user['wallet']['rmb'],
					-($money + $charge),
					$user['wallet']['rmb'] - ($money + $charge),
				],
			]);
			// 提交事务
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return json([
				'code'		=>	555,
				'message'	=>	$e->getMessage()
			]);
		}
		// 操作成功
		return json([
			'code'		=>	200,
			'message'	=>	'恭喜您、操作成功！'
		]);
	}

	/**
	 * 历史记录
	 */
	public function history(Request $req)
	{
		// 自己的账号
        $username = session('user.account.username');
        // 分页数据
        $page = $req->param('page/d', 1);
        $size = $req->param('size/d', 20);
        $offset = $page - 1 < 0 ? 0 : ($page - 1) * $size;
        // 查询对象
        $query = Db::table('withdraw')->field('wid AS id, status, channel, number, charge, update_at AS date')->where('username', '=', $username);
        // 查询数据
        $data = $query->limit($offset, $size)->order('create_at DESC')->select();
        // 返回数据
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
            'data'      =>  $data
        ]);
	}
}