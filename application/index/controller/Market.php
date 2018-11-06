<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use think\facade\Env;

class Market extends Base
{
	/**
	 * 订单类型
	 */
	const TYPE_BUY						=	1;			// 求购类的订单
	const TYPE_SELL						=	2;			// 出售类的订单

	/**
	 * 订单状态
	 */
	const STATUS_CANCEL					=	0;			// 已取消
	const STATUS_NORMAL					=	1;			// 匹配中
	const STATUS_WAIT_FOR_PAYMENT		=	2;			// 等待付款中
	const STATUS_WAIT_FOR_CONFIRM		=	3;			// 等待确认中
	const STATUS_IN_THE_COMPLAINT		=	4;			// 在投诉中
	const STATUS_SUCCESS				=	8;			// 已成交

	/**
	 * 交易命令
	 */
	const COMMAND_CANCEL				=	0;			// 取消订单
	const COMMAND_RECEIVE				=	1;			// 接单
	const COMMAND_PAYMENT				=	2;			// 付款
	const COMMAND_URGE					=	3;			// 催款
	const COMMAND_REPORT				=	4;			// 投诉
	const COMMAND_PROOF					=	5;			// 举证
	const COMMAND_QUIT					=	6;			// 退出交易
    const COMMAND_SUCCESS               =   8;          // 确认收货
    const COMMAND_SYSTEM                =   18;         // 系统提示
    const COMMAND_BUYER_WIN             =   30;         // 买方胜利
    const COMMAND_SELLER_WIN            =   31;         // 卖方胜利
	const COMMAND_FORCE_CANCEL			=	32;			// 强制取消



	// +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------

    /**
     * 生成TID
     */
    public function generateTID()
    {
        $prefix = chr(mt_rand(65, 90));
        do {
            $number = mt_rand(100000000, 999999999);
            $mid = $prefix . $number;
        } while (!empty(Db::table('uuid')->where('id', '=', $mid)->find()));
        $bool = Db::table('uuid')->insert(['id' => $mid, 'type' => 3]);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、订单编号生成失败！");
        }
        return $mid;
    }

    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------

    /**
     * 发布订单
     */
    public function post($type, $username, $safeword, $number, $price)
    {
        // 开盘时间
        $times = config('hello.trade.time');
        if (!empty($times)) {
            $current_time = date('H:i:s');
            $isOk = false;
            foreach ($times as $key => $time) {
                if ($current_time >= $time['open'] && $current_time <= $time['close']) {
                    $isOk = true;
                    break;
                } else if ($current_time < $time['open']) {
                    throw new \think\Exception("很抱歉、市场已关闭下一个开放时间点为：" . $time['open']);
                }
            }
            if (!$isOk) {
                throw new \think\Exception("很抱歉、市场已关闭请明天再来！");
            }
        }
		// 用户对象
		$ac = new Account();
		// 用户账号
		$user = $ac->instance($username, null, $safeword, true);
		if (empty($user)) {
			throw new \think\Exception('很抱歉、安全密码不正确！');
		}
		if (empty($user['account']['status'])) {
			throw new \think\Exception('很抱歉、您的账号已被冻结！');
		}
    	if ($user['account']['authen'] != 1) {
			throw new \think\Exception('很抱歉、您需要先完成实名认证！');
    	}
    	// 判断数量
    	if (empty($number) || $number <= 0) {
			throw new \think\Exception('很抱歉、错误的数量！');
    	}
    	if ($number < config('hello.trade.' . $type . '.number.min')) {
			throw new \think\Exception('很抱歉、每次交易数量最少' . config('hello.trade.' . $type . '.number.min') . '个！');
    	}
    	if ($number > config('hello.trade.' . $type . '.number.max')) {
			throw new \think\Exception('很抱歉、每次交易数量最多' . config('hello.trade.' . $type . '.number.max') . '个！');
    	}
    	// 判断单价
    	if (empty($price) || $price <= 0) {
    		throw new \think\Exception('很抱歉、错误的价格！');
    	}
    	// 查找行情
    	$market = Db::table('market')->where('date', '=', date('Y-m-d'))->find();
    	if (empty($market)) {
    		throw new \think\Exception('很抱歉、暂未开盘！');
    	}
    	// 行情价格
    	if ($price > $market['high']) {
    		throw new \think\Exception('很抱歉、价格不能高于【' . money($market['high']) . '】！');
    	}
    	if ($price < $market['low']) {
    		throw new \think\Exception('很抱歉、价格不能低于【' . money($market['low']) . '】！');
    	}
    	// 订单太多
        $limit = config('hello.trade.limit');
    	if (!empty($limit)) {
            $count = Db::table('trade')->where('owner', '=', $username)
                    ->where('type', '=', $type == 'buy' ? self::TYPE_BUY : self::TYPE_SELL)
                    ->where('status', '<>', 0)->where('status', '<>', 8)
                    ->count('id');
            if ($count >= $limit) {
    		    throw new \think\Exception('很抱歉、请先处理未成交的订单！');
            }
    	}
    	// 实际买方要支付的现金
    	$rmb = $price * $number;
    	// 交易手续费
    	$charge = (config('hello.trade.charge') ?: 0) * $number;
    	// 判断余额
        $is_rmb = 0;
        $is_deposit = 0;
    	if ($type == 'sell') {
    		// 余额判断
    		if ($user['wallet']['money'] < $number) {
    			throw new \think\Exception('很抱歉、您的可用资金不足！');
    		}
    		// 交易手续费
    		if ($user['wallet']['money'] < $number + $charge) {
    			throw new \think\Exception('很抱歉、您的可用资金不足以支付手续费！');
    		}
    		// 更新金额
    		(new Wallet())->change($username, 11, [
    			1 	=>	[$user['wallet']['money'], -($number + $charge), $user['wallet']['money'] - $number - $charge],
    			2 	=>	[$user['wallet']['deposit'], $number + $charge, $user['wallet']['deposit'] + $number + $charge],
    		]);
            // 已扣押金
            $is_deposit = 1;
    	} else {
            // 强制使用RMB
            if (!empty(config('hello.trade.force_rmb'))) {
                // 余额判断
                $myRmb = $user['wallet']['rmb'];
                if (strval($rmb) != $myRmb && $myRmb < $rmb) {
                    throw new \think\Exception('很抱歉、您的余额不足！');
                }
                // 扣除人民币
                (new Wallet())->change($user['account']['username'], 10, [
                    8       =>  [
                        $user['wallet']['rmb'],
                        -$rmb,
                        $user['wallet']['rmb'] - $rmb,
                    ],
                ]);
                // 已扣人民币
                $is_rmb = 1;
            }
        }
    	// 添加订单
    	$bool = Db::table('trade')->insert([
    		'tid'			=>	$this->generateTID(),
    		'type'			=>	$type == 'buy' ? self::TYPE_BUY : self::TYPE_SELL,
    		'status'		=>	self::STATUS_NORMAL,
            'is_rmb'        =>  $is_rmb,
            'is_deposit'    =>  $is_deposit,
    		'number'		=>	$number,
    		'price'			=>	$price,
    		'charge'		=>	$charge,
    		'owner'			=>	$username,
    		'target'		=>	null,
    		'secret'		=>	strtoupper(base_convert(time(), 10, 36)),
    		'create_at'		=>	$this->timestamp,
    		'update_at'		=>	$this->timestamp,
    	]);
    	if (empty($bool)) {
    		throw new \think\Exception('很抱歉、订单创建失败！');
    	}
    	// 行情更新
    	$bool = Db::table('market')->where('id', '=', $market['id'])->update([
    		$type . '_count'		=>	Db::raw($type . '_count+1'),
    		$type . '_money'		=>	Db::raw($type . '_money+' . $number),
    		'update_at'		=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \think\Exception('很抱歉、行情更新失败！');
    	}
        // 操作日志
        $this->log($type == 'buy' ? 60 : 61, null, $username);
    }

    /**
     * 取消订单
     * 命令：0
     * 状态：0
     */
    public function cancel($trade, $target, $isTrial = false)
    {
        // 非管理操作、需要进行验证
        if (!$isTrial) {
            // 操作者账号
            $username = $target['account']['username'];
            // 根据订单状态判断身份验证资格
            switch ($trade['status']) {
                // 当前在匹配中
                case self::STATUS_NORMAL:
                    // 仅拥有者可以取消
                    if ($username != $trade['owner']) {
                        throw new \think\Exception("很抱歉、您不能取消别人的订单！");
                    }
                    // 30分钟的保护时间，倒计时进行中
                    $timelength = time() - strtotime($trade['update_at']);
                    // 是否在安全保护时间内
                    $isSafetime = $timelength < 1800;
                    if ($isSafetime) {
                        throw new \think\Exception("很抱歉、请在" . (1800 - $timelength) . "秒后再执行该操作！");
                    }
                    break;
                // 等待买家付款
                case self::STATUS_WAIT_FOR_PAYMENT:
                    // 30分钟的保护时间，倒计时进行中
                    $timelength = time() - strtotime($trade['update_at']);
                    // 是否在安全保护时间内
                    $isSafetime = $timelength < 1800;

                    if ($trade['type'] == self::TYPE_BUY && $trade['owner'] == $username) {
                        // 1. 求购订单，发布者可以立即取消订单，反正发布者还没付款
                    } else if ($trade['type'] == self::TYPE_BUY && $trade['target'] == $username) {
                        // 2. 求购订单，接单者只能安全时间后才能取消订单
                        if ($isSafetime) {
                            throw new \think\Exception("很抱歉、请在" . (1800 - $timelength) . "秒后再执行该操作！");
                        }
                    } else if ($trade['type'] == self::TYPE_SELL && $trade['owner'] == $username) {
                        // 3. 出售订单，发布者需要安全时间后才能取消订单
                        if ($isSafetime) {
                            throw new \think\Exception("很抱歉、请在" . (1800 - $timelength) . "秒后再执行该操作！");
                        }
                    } else {
                        // 出售订单，接单者只能退出交易，无法取消
                        throw new \think\Exception("很抱歉、您不能取消该订单！");
                    }
                    break;
                // 其他情况
                default:
                    throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
                    break;
            }
        } else {
            // 管理员操作
            $username = '00000000000';
        }
        // 找到买家账号
        if ($trade['type'] == self::TYPE_BUY && !empty($trade['is_rmb'])) {
            // 找到买家
            $buyer = (new Account())->instance($trade['owner']);
            // 增加人民币
            (new Wallet())->change($trade['owner'], 12, [
                8       =>  [
                    $buyer['wallet']['rmb'],
                    $trade['number'] * $trade['price'],
                    $buyer['wallet']['rmb'] + $trade['number'] * $trade['price'],
                ],
            ]);
        }
    	// 找到卖家账号
    	$seller_username = null;
    	if ($trade['type'] == self::TYPE_BUY && !empty($trade['target'])) {
    		$seller_username = $trade['target'];
    	} else if ($trade['type'] == self::TYPE_SELL) {
    		$seller_username = $trade['owner'];
    	}
    	// 存在卖家账号，则说明需要退款
    	if (!empty($seller_username)) {
    		// 确认卖家数据
    		$seller = $target;
    		if (is_null($seller) || $seller_username != $target['account']['username']) {
    			$seller = (new Account())->instance($seller_username);
    		}
    		// 执行退款
    		$number = $trade['number'];
    		$charge = $trade['charge'];
    		(new Wallet())->change($seller_username, 12, [
    			1	=>	[$seller['wallet']['money'], $number + $charge, $seller['wallet']['money'] + $number + $charge],
    			2	=>	[$seller['wallet']['deposit'], -($number + $charge), $seller['wallet']['deposit'] - $number - $charge],
    		]);
    	}
    	// 修改订单状态
    	$bool = Db::table('trade')->update([
    		'id'		=>	$trade['id'],
    		'status'	=>	self::STATUS_CANCEL,
    		'update_at'	=>	$this->timestamp,
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、订单状态更新失败！");
    	}
    	// 添加交易记录
    	$bool = Db::table('trade_log')->insert([
    		'tid'		=>	$trade['tid'],
    		'secret'	=>	$trade['secret'],
    		'command'	=>	$isTrial ? self::COMMAND_FORCE_CANCEL : self::COMMAND_CANCEL,
    		'username'	=>	$username,
    		'content'	=>	null,
    		'create_at'	=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、更新交易记录失败！");
    	}
    }

    /**
     * 接受订单
     * 命令：1
     * 状态：2
     */
    public function receive($trade, $target)
    {
        // 开盘时间
        $times = config('hello.trade.time');
        if (!empty($times)) {
            $current_time = date('H:i:s');
            $isOk = false;
            foreach ($times as $key => $time) {
                if ($current_time >= $time['open'] && $current_time <= $time['close']) {
                    $isOk = true;
                    break;
                } else if ($current_time < $time['open']) {
                    throw new \think\Exception("很抱歉、市场已关闭下一个开放时间点为：" . $time['open']);
                }
            }
            if (!$isOk) {
                throw new \think\Exception("很抱歉、市场已关闭请明天再来！");
            }
        }
    	// 操作者账号
    	$username = $target['account']['username'];
        // 订单太多
        $limit = config('hello.trade.limit');
        if (!empty($limit)) {
            $count = Db::table('trade')->where('owner', '=', $username)
                    ->where('status', '<>', 0)->where('status', '<>', 8)
                    ->count('id');
            if ($count >= $limit) {
                throw new \think\Exception('很抱歉、请先处理未成交的订单！');
            }
        }
    	// 自己不能接单
    	if ($trade['owner'] == $username) {
    		throw new \think\Exception("很抱歉、您不能与自己交易！");
    	}
    	// 重复接单
    	if ($trade['target'] == $username) {
    		throw new \think\Exception("很抱歉、请勿重复接单！");
    	}
    	// 已被抢单
    	if ($trade['status'] != self::STATUS_NORMAL) {
    		throw new \think\Exception("很抱歉、该订单已被别人抢先一步！");
    	}
        // 订单金额
        $number = $trade['number'];
        $price = $trade['price'];
        $charge = $trade['charge'];
        $rmb = $number * $price;
        // 强制使用RMB
        $force_rmb = config('hello.trade.force_rmb');
        // 资金对象
        $wl = new Wallet();
        // 查询发布者的资料
        $owner = (new Account())->instance($trade['owner']);
        // 买家身份
        $buyer = null;
        // 卖家身份
        $seller = null;
        // 是否直接交易成功
        $is_success = false;
    	// 如果是求购订单
    	if ($trade['type'] == self::TYPE_BUY) {
            // 保存身份
            $buyer = $owner;
            $seller = $target;
            // 顺便判断卖家的余额
            if ($seller['wallet']['money'] < $number + $charge) {
                throw new \think\Exception("很抱歉、您的可用资金不足！");
            }
            // 如果使用RMB
            if (!empty($trade['is_rmb'])) {
                // 先将货币给买家
                $buyerData = [
                    1       =>  [
                        $buyer['wallet']['money'],
                        $number,
                        $buyer['wallet']['money'] + $number
                    ],
                ];
                $wl->change($buyer['account']['username'], 10, $buyerData);
                // 再将钱给卖家，同时扣除卖家的货币
                $sellerData = [
                    1       =>  [
                        $seller['wallet']['money'],
                        -($number + $charge),
                        $seller['wallet']['money'] - ($number + $charge)
                    ],
                    8       =>  [
                        $seller['wallet']['rmb'],
                        $rmb,
                        $seller['wallet']['rmb'] + $rmb,
                    ],
                ];
                $wl->change($seller['account']['username'], 11, $sellerData);
                // 最后将订单标记为成功
                $is_success = true;
            }
        }else {
            // 保存身份
            $buyer = $target;
            $seller = $owner;
            // 如果强制使用RMB
            if (!empty($force_rmb)) {
                // 判断买家的余额是否足够
                if ($buyer['wallet']['rmb'] < $rmb) {
                    throw new \think\Exception("很抱歉、您的余额不足！");
                }
                // 先扣除买家的余额，同时给买家货币
                $buyerData = [
                    1       =>  [
                        $buyer['wallet']['money'],
                        $number,
                        $buyer['wallet']['money'] + $number
                    ],
                    8       =>  [
                        $buyer['wallet']['rmb'],
                        -$rmb,
                        $buyer['wallet']['rmb'] - $rmb
                    ],
                ];
                $wl->change($buyer['account']['username'], 10, $buyerData);
                // 再扣除卖家的货币
                $sellerData = [
                    2       =>  [
                        $seller['wallet']['deposit'],
                        -($number + $charge),
                        $seller['wallet']['deposit'] - ($number + $charge)
                    ],
                    8       =>  [
                        $seller['wallet']['rmb'],
                        $rmb,
                        $seller['wallet']['rmb'] + $rmb
                    ],
                ];
                $wl->change($seller['account']['username'], 11, $sellerData);
                // 最后将订单标记为成功
                $is_success = true;
            }
        }
        // 如果直接交易成功
        if (!empty($is_success)) {
            // 修改订单状态
            $bool = Db::table('trade')->update([
                'id'        =>  $trade['id'],
                'status'    =>  self::STATUS_SUCCESS,
                'target'    =>  ($trade['type'] == self::TYPE_BUY ? $seller['account']['username'] : $buyer['account']['username']),
                'update_at' =>  $this->timestamp,
            ]);
            if (empty($bool)) {
                throw new \Exception("很抱歉、更新订单状态失败！");
            }
            // 添加交易记录
            $bool = Db::table('trade_log')->insert([
                'tid'       =>  $trade['tid'],
                'secret'    =>  $trade['secret'],
                'command'   =>  self::COMMAND_SUCCESS,
                'username'  =>  ($trade['type'] == self::TYPE_BUY ? $seller['account']['username'] : $buyer['account']['username']),
                'content'   =>  null,
                'create_at' =>  $this->timestamp
            ]);
            if (empty($bool)) {
                throw new \Exception("很抱歉、更新交易记录失败！");
            }
            // 行情更新
            $bool = Db::table('market')->where('date', '=', date('Y-m-d'))->update([
                'success_count'     =>  Db::raw('success_count+1'),
                'success_money'     =>  Db::raw('success_money+' . $number),
                'charge'            =>  Db::raw('charge+' . $charge),
                'update_at'         =>  $this->timestamp
            ]);
            if (empty($bool)) {
                throw new \think\Exception('很抱歉、行情更新失败！');
            }
            // 上级交易分红
            $inviter = (new Account())->value($seller['account']['username'], 'inviter');
            if (!empty($inviter)) {
                $this->team_bonus($inviter, 1, $charge);
            }
            // 返回结果
            return 'SUCCESS';
        } else {
            // 冻结卖家的此次交易金额
            (new Wallet())->change($username, 11, [
                1   =>  [$seller['wallet']['money'], -($number + $charge), $seller['wallet']['money'] - ($number + $charge)],
                2   =>  [$seller['wallet']['deposit'], $number + $charge, $seller['wallet']['deposit'] + $number + $charge],
            ]);
        	// 修改订单状态
        	$bool = Db::table('trade')->update([
        		'id'		=>	$trade['id'],
                'is_deposit'=>  1,
        		'status'	=>	self::STATUS_WAIT_FOR_PAYMENT,
        		'target'	=>	$username,
        		'update_at'	=>	$this->timestamp,
        	]);
        	if (empty($bool)) {
        		throw new \Exception("很抱歉、订单状态更新失败！");
        	}
        	// 添加交易记录
        	$bool = Db::table('trade_log')->insert([
        		'tid'		=>	$trade['tid'],
        		'secret'	=>	$trade['secret'],
        		'command'	=>	self::COMMAND_RECEIVE,
        		'username'	=>	$username,
        		'content'	=>	null,
        		'create_at'	=>	$this->timestamp
        	]);
        	if (empty($bool)) {
        		throw new \Exception("很抱歉、更新交易记录失败！");
        	}
            // 返回结果
            return 'WAITING';
        }
    }

    /**
     * 订单付款
     * 命令：2
     * 状态：3
     */
    public function payment($trade, $target)
    {
    	// 操作者账号
    	$username = $target['account']['username'];
    	// 身份判断
    	if ($trade['type'] == self::TYPE_BUY && $username == $trade['owner']) {
    		// 求购订单，我是发布者，即买家，我可以付款
    	} else if ($trade['type'] == self::TYPE_SELL && $username == $trade['target']) {
    		// 出售订单，我是接单者，即买家，我可以付款
    	} else {
    		throw new \think\Exception("很抱歉、您不能执行该操作！");
    	}
    	// 状态判断
    	if ($trade['status'] != self::STATUS_WAIT_FOR_PAYMENT) {
    		throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
    	}
    	// 修改订单状态
    	$bool = Db::table('trade')->update([
    		'id'		=>	$trade['id'],
    		'status'	=>	self::STATUS_WAIT_FOR_CONFIRM,
    		'update_at'	=>	$this->timestamp,
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、订单状态更新失败！");
    	}
    	// 添加交易记录
    	$bool = Db::table('trade_log')->insert([
    		'tid'		=>	$trade['tid'],
    		'secret'	=>	$trade['secret'],
    		'command'	=>	self::COMMAND_PAYMENT,
    		'username'	=>	$username,
    		'content'	=>	null,
    		'create_at'	=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、更新交易记录失败！");
    	}
    }

    /**
     * 催付，我没收到钱
     * 命令：3
     * 状态：不更改
     */
    public function urge($trade, $target)
    {
    	// 操作者账号
    	$username = $target['account']['username'];
    	// 只有等待付款或等待确认的订单才可以执行
    	if ($trade['status'] != self::STATUS_WAIT_FOR_PAYMENT && $trade['status'] != self::STATUS_WAIT_FOR_CONFIRM) {
    		throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
    	}
    	// 身份判断
    	if ($trade['type'] == self::TYPE_BUY && $username == $trade['target']) {
    		// 求购订单，我是接单者，即卖家，我可以催付
    	} else if ($trade['type'] == self::TYPE_SELL && $username == $trade['owner']) {
    		// 出售订单，我是发单者，即卖家，我可以催付
    	} else {
    		throw new \think\Exception("很抱歉、您不能执行该操作！");
    	}
    	// 添加交易记录
    	$bool = Db::table('trade_log')->insert([
    		'tid'		=>	$trade['tid'],
    		'secret'	=>	$trade['secret'],
    		'command'	=>	self::COMMAND_URGE,
    		'username'	=>	$username,
    		'content'	=>	null,
    		'create_at'	=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、更新交易记录失败！");
    	}
    }

    /**
     * 投诉举报
     * 命令：4
     * 状态：4
     */
    public function report($trade, $target)
    {
    	// 操作者账号
    	$username = $target['account']['username'];
    	// 身份判断
    	if ($trade['owner'] != $username && $trade['target'] != $username) {
    		throw new \think\Exception("很抱歉、您没有权限执行该操作！");
    	}
    	// 订单状态判断
    	if ($trade['status'] != self::STATUS_WAIT_FOR_PAYMENT && $trade['status'] != self::STATUS_WAIT_FOR_CONFIRM) {
    		throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
    	}
    	// 修改订单状态
    	$bool = Db::table('trade')->update([
    		'id'		=>	$trade['id'],
    		'status'	=>	self::STATUS_IN_THE_COMPLAINT,
    		'update_at'	=>	$this->timestamp,
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、订单状态更新失败！");
    	}
    	// 添加交易记录
    	$bool = Db::table('trade_log')->insert([
    		'tid'		=>	$trade['tid'],
    		'secret'	=>	$trade['secret'],
    		'command'	=>	self::COMMAND_REPORT,
    		'username'	=>	$username,
    		'content'	=>	null,
    		'create_at'	=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、更新交易记录失败！");
    	}
    }

    /**
     * 举证
     * 命令：5
     * 状态：不更改
     */
    public function proof($trade, $target, $content)
    {
    	// 操作者账号
    	$username = $target['account']['username'];
    	// 身份判断
    	if ($trade['owner'] != $username && $trade['target'] != $username) {
    		throw new \think\Exception("很抱歉、您没有权限执行该操作！");
    	}
    	// 订单状态判断
    	if ($trade['status'] != self::STATUS_WAIT_FOR_PAYMENT && $trade['status'] != self::STATUS_WAIT_FOR_CONFIRM && $trade['status'] != self::STATUS_IN_THE_COMPLAINT) {
    		throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
    	}
    	// 添加交易记录
    	$bool = Db::table('trade_log')->insert([
    		'tid'		=>	$trade['tid'],
    		'secret'	=>	$trade['secret'],
    		'command'	=>	self::COMMAND_PROOF,
    		'username'	=>	$username,
    		'content'	=>	$content,
    		'create_at'	=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、更新交易记录失败！");
    	}
    }

    /**
     * 退出交易
     */
    public function quit($trade, $target, $isTrial = false)
    {
    	// 操作者账号
    	$username = $target['account']['username'];
    	// 身份判断
    	if ($trade['target'] != $username) {
    		throw new \think\Exception("很抱歉、您没有权限执行该操作！");
    	}
    	// 订单状态判断
    	if ($trade['status'] != self::STATUS_WAIT_FOR_PAYMENT) {
            if ($trade['status'] == self::STATUS_IN_THE_COMPLAINT && $isTrial) {
                // 管理员在执行审判，允许通过
            } else {
    		    throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
            }
    	}
    	// 如果是买入的订单，出售者需要等待保护时间
    	if ($trade['type'] == self::TYPE_BUY && !$isTrial) {
	    	// 30分钟的保护时间，倒计时进行中
	    	$timelength = time() - strtotime($trade['update_at']);
	    	// 是否在安全保护时间内
	    	$isSafetime = $timelength < 1800;
	    	// 时间判断
	    	if ($isSafetime) {
	    		throw new \think\Exception("很抱歉、请在" . (1800 - $timelength) . "秒后再执行该操作！");
	    	}
    	}
    	// 修改订单状态
    	$bool = Db::table('trade')->update([
    		'id'		=>	$trade['id'],
    		'status'	=>	self::STATUS_NORMAL,
    		'target'	=>	null,
    		'secret'	=>	strtoupper(base_convert(time(), 10, 36)),
    		'update_at'	=>	$this->timestamp,
    	]);
    	if (empty($bool)) {
    		throw new \Exception("很抱歉、订单状态更新失败！");
    	}
        // 买入订单，卖家退出交易需要退钱
        if ($trade['type'] == self::TYPE_BUY) {
            // 确认卖家数据
            $seller = $target;
            $seller_username = $target['account']['username'];
            // 执行退款
            $number = $trade['number'];
            $charge = $trade['charge'];
            (new Wallet())->change($seller_username, 12, [
                1   =>  [$seller['wallet']['money'], $number + $charge, $seller['wallet']['money'] + $number + $charge],
                2   =>  [$seller['wallet']['deposit'], -($number + $charge), $seller['wallet']['deposit'] - $number - $charge],
            ]);
        } else {
            // 卖出订单，买家退出就什么都不用干
        }
    	// 添加交易记录
        if (!$isTrial) {
        	$bool = Db::table('trade_log')->insert([
        		'tid'		=>	$trade['tid'],
        		'secret'	=>	$trade['secret'],
        		'command'	=>	self::COMMAND_QUIT,
        		'username'	=>	$username,
        		'content'	=>	null,
        		'create_at'	=>	$this->timestamp
        	]);
        	if (empty($bool)) {
        		throw new \Exception("很抱歉、更新交易记录失败！");
        	}
        }
    }

    /**
     * 交易成功，卖家来确认收到钱了
     * 命令：8
     * 状态：8
     */
    public function confirm($trade, $target, $isTrial = false)
    {
    	// 操作者账号
    	$username = $target['account']['username'];
    	// 只有等待确认或投诉中的订单才可以执行
    	if ($trade['status'] != self::STATUS_WAIT_FOR_CONFIRM && $trade['status'] != self::STATUS_IN_THE_COMPLAINT) {
    		throw new \think\Exception("很抱歉、当前状态下无法执行该操作！");
    	}
    	// 身份判断
    	if ($trade['type'] == self::TYPE_BUY && $username == $trade['target']) {
    		// 求购订单，我是接单者，即卖家，我可以确认收货
    	} else if ($trade['type'] == self::TYPE_SELL && $username == $trade['owner']) {
    		// 出售订单，我是发单者，即卖家，我可以确认收货
    	} else {
    		throw new \think\Exception("很抱歉、您不能执行该操作！");
    	}
    	// 交易金额
    	$number = $trade['number'];
    	$charge = $trade['charge'];
    	// 找到买家，将钱转给他
    	$buyer = null;
    	$buyer_username = null;
    	if ($trade['type'] == self::TYPE_BUY) {
    		$buyer_username = $trade['owner'];
    		$buyer = (new Account())->instance($buyer_username);
    	} else {
    		$buyer_username = $trade['target'];
    		$buyer = (new Account())->instance($buyer_username);
    	}
    	if (empty($buyer) || empty($buyer_username)) {
    		throw new \think\Exception("很抱歉、买家信息确认失败！");
    	}
    	(new Wallet())->change($buyer_username, 10, [
    		1	=>	[$buyer['wallet']['money'], $number, $buyer['wallet']['money'] + $number],
    	]);
    	// 找到卖家，将钱扣除掉
    	$seller = $target;
    	$seller_username = $username;
    	(new Wallet())->change($seller_username, 11,
            [
        		2	=>	[
                    $seller['wallet']['deposit'],
                    -($number + $charge),
                    $seller['wallet']['deposit'] - $number - $charge,
                ],
            ],
            ['number' => $number]
    	);
    	// 修改订单状态
    	$bool = Db::table('trade')->update([
    		'id'		=>	$trade['id'],
    		'status'	=>	self::STATUS_SUCCESS,
    		'update_at'	=>	$this->timestamp,
    	]);
    	// 添加交易记录
        if (!$isTrial) {
        	$bool = Db::table('trade_log')->insert([
        		'tid'		=>	$trade['tid'],
        		'secret'	=>	$trade['secret'],
        		'command'	=>	self::COMMAND_SUCCESS,
        		'username'	=>	$username,
        		'content'	=>	null,
        		'create_at'	=>	$this->timestamp
        	]);
        	if (empty($bool)) {
        		throw new \Exception("很抱歉、更新交易记录失败！");
        	}
        }
    	// 行情更新
    	$bool = Db::table('market')->where('date', '=', date('Y-m-d'))->update([
    		'success_count'		=>	Db::raw('success_count+1'),
    		'success_money'		=>	Db::raw('success_money+' . $number),
    		'charge'			=>	Db::raw('charge+' . $trade['charge']),
    		'update_at'			=>	$this->timestamp
    	]);
    	if (empty($bool)) {
    		throw new \think\Exception('很抱歉、行情更新失败！');
    	}
    	// 上级交易分红
    	$inviter = (new Account())->value($seller_username, 'inviter');
    	if (!empty($inviter)) {
    		$this->team_bonus($inviter, 1, $trade['charge']);
    	}
    }

    /**
     * 递归：团队收益
     * @param  $index   第几层上级
     * @param  $bonus   本次交易服务费是多少
     */
    public function team_bonus($inviter, $index, $bonus)
    {
        // 获取上级的信息
        $user = (new Account())->instance($inviter);
        // 上级级别
        $level = $user['account']['type'];
        // 查询该级别的配置
        $config = config('hello.level.'.$level);
        // 存在该级别的配置，并且该级别拥有团队交易利益配置
        if (!empty($config) && !empty($config['trade'])) {
            // 拥有这一代的配置
            if (array_key_exists($index, $config['trade'])) {
                // 我能拿多少比例
                $trade_rate = $config['trade'][$index];
                if ($trade_rate > 0) {
                    // 实际拿到的利润
                    $money = $trade_rate * $bonus;
                    if ($money > 0) {
                        // 加钱
                        (new Wallet())->change($inviter, 31, [
                            1   =>  [$user['wallet']['money'], $money, $user['wallet']['money'] + $money]
                        ]);
                    }
                    // 继续给上级的上级加钱
                    if (!empty($user['account']['inviter'])) {
                        $this->team_bonus($user['account']['inviter'], $index + 1, $bonus);
                    }
                }
            }
        }
    }


    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

	/**
	 * 行情首页
	 */
    public function index()
    {
        // 交易配置
        $config = config('hello.trade');
        $this->assign('config', $config);
        // 显示页面
        return $this->fetch();
    }

    /**
	 * 行情概览
	 */
    public function overview()
    {
    	// 历史行情
    	$markets = Db::table('market')->field('date, price, high, low')->where('date', '<=', date('Y-m-d'))->order('date DESC')->limit(7)->select();
    	if (empty($markets)) {
    		$markets[] = [
    			'date'	=>	date('Y-m-d'),
    			'price'	=>	2,
    			'high'	=>	2,
    			'low'	=>	2,
    		];
    	}
    	// 不足一周则补齐
    	$count = count($markets);
		for ($i = $count; $i < 7; $i++) {
    		$count = count($markets);
            $price = $markets[$count - 1]['price'] - 0.02;
            $price = $price <= 0 ? 0 : $price;
			array_push($markets, [
				'date'	=>	date('Y-m-d', strtotime($markets[$count - 1]['date']) - 86400),
				'price'	=>	$price,
				'high'	=>	$price,
				'low'	=>	$price,
			]);
		}
		// 数据格式化
		foreach ($markets as $key => $value) {
			$markets[$key]['price'] = money($markets[$key]['price']);
			$markets[$key]['high'] = money($markets[$key]['high']);
			$markets[$key]['low'] = money($markets[$key]['low']);
		}
		// 返回数据
        return json([
        	'code'		=>	200,
    		'message'	=>	'恭喜您、操作成功！',
    		'data'		=>	[
    			'market'	=>	$markets,
    			'charge'	=>	config('hello.trade.charge'),
    		]
        ]);
    }

    /**
     * 列表数据
     */
    public function search(Request $req)
    {
    	// 分页数据
    	$page = $req->param('page/d', 1);
    	$size = $req->param('size/d', 400);
    	$offset = $page - 1 < 0 ? 0 : ($page - 1) * $size;
        // 查询对象
        $query = Db::table('trade')->alias('t')
                    ->join('profile p', 'p.username = t.owner')
                    ->field('tid AS id, t.type, t.status, p.nickname AS owner, number, price, t.update_at AS date');
        // 按类型查询
        switch ($req->param('type/d', 1)) {
            // 买入
            case 1:
                // 查询数据
                $data = $query->where('t.type', '=', 1)
                    ->where('t.status', '<>', 0)
                    ->where('t.status', '<>', 8)
                    ->order('t.status ASC, t.create_at DESC')
                    ->limit($offset, $size)->select();
                break;
            // 卖出
            case 2:
                // 查询数据
                $data = $query->where('t.type', '=', 2)
                    ->where('t.status', '<>', 0)
                    ->where('t.status', '<>', 8)
                    ->order('t.status ASC, t.create_at DESC')
                    ->limit($offset, $size)->select();
                break;
            // 自己的
            case 3:
                // 我的账号
                $username = session('user.account.username');
                if (empty($username)) {
                    return json([
                        'code'      =>  500,
                        'message'   =>  '很抱歉、请重新登录！'
                    ]);
                }
                // 查询数据
                $data = $query->where('owner|target', '=', $username)->where('t.status', '<>', 8)
                    ->order('t.status DESC, t.update_at DESC')
                    ->limit($offset, $size)->select();
                if (empty($data) || count($data) < $size) {
                    // 原数据总数
                    $total = Db::table('trade')->where('owner|target', '=', $username)->where('status', '<>', 8)->count('id');
                    // 重新计算偏移值
                    $offset -= $total;
                    // 偏移值太小，说明是第一页，原数据都不够凑齐一页
                    if ($offset < 0) {
                        // 那么本次拉取的只需要一部分
                        $size += $offset;
                        // 偏移值改为0
                        $offset = 0;
                    }
                    // 查询成功的数据
                    $dataTemp = Db::table('trade')->alias('t')
                                ->join('profile p', 'p.username = t.owner')
                                ->field('tid AS id, t.type, t.status, p.nickname AS owner, number, price, t.update_at AS date')
                                ->where('owner|target', '=', $username)->where('t.status', '=', 8)
                                ->order('t.update_at DESC')
                                ->limit($offset, $size)->select();
                    // 合并结果
                    $data = array_merge($data, $dataTemp);
                }
                break;
            // 其他
            default:
                # code...
                break;
        }
    	// 数据格式化
    	foreach ($data as $key => $value) {
    		$data[$key]['price'] = money($value['price']);
    		$data[$key]['number'] = money($value['number']);
    	}
    	// 返回数据
    	return json([
    		'code'		=>	200,
    		'message'	=>	'恭喜您、操作成功！',
    		'data'		=>	$data
    	]);
    }

    /**
     * 买入订单
     */
    public function buy(Request $req)
    {
    	try {
    		// 开启事务
    		Db::startTrans();
    		// 用户账号
    		$username = session('user.account.username');
	    	// 获取数量
	    	$number = $req->param('number/f');
	    	// 获取单价
            $price = $req->param('price/f');
            // 安全密码
	    	$safeword = $req->param('safeword');
	    	// 订单处理
	    	$this->post('buy', $username, $safeword, $number, $price);
    		// 提交事务
    		Db::commit();
    	} catch (\Exception $e) {
    		Db::rollback();
    		return json([
    			'code'			=>	555,
    			'message'		=>	$e->getMessage()
    		]);
    	}
    	// 操作成功
    	return json([
    		'code'				=>	200,
    		'message'			=>	'恭喜您、操作成功！'
    	]);
    }

    /**
     * 卖出订单
     */
    public function sell(Request $req)
    {
    	try {
    		// 开启事务
    		Db::startTrans();
    		// 用户账号
    		$username = session('user.account.username');
	    	// 获取数量
	    	$number = $req->param('number/f');
	    	// 获取单价
	    	$price = $req->param('price/f');
            // 安全密码
            $safeword = $req->param('safeword');
	    	// 订单处理
	    	$this->post('sell', $username, $safeword, $number, $price);
    		// 提交事务
    		Db::commit();
    	} catch (\Exception $e) {
    		Db::rollback();
    		return json([
    			'code'			=>	555,
    			'message'		=>	$e->getMessage()
    		]);
    	}
    	// 操作成功
    	return json([
    		'code'				=>	200,
    		'message'			=>	'恭喜您、操作成功！'
    	]);
    }

    /**
     * 交易详情
     */
    public function transaction(Request $req)
    {
    	if ($req->isPost()) {
    		// 用户对象
    		$ac = new Account();
            // 访客模式、目前仅限管理员
            $guest = $req->param('guest');
            if (empty($guest)) {
                // 用户账号
                $username = session('user.account.username');
        		// 用户账号
        		$user = $ac->instance($username, null, null, true);
        		if (empty($user)) {
        			return json([
        				'code'		=>	501,
        				'message'	=>	'很抱歉、请重新登录！'
        			]);
        		}
            }
    		// 订单编号
    		$id = $req->param('id');
    		if (empty($id)) {
    			return json([
    				'code'		=>	502,
    				'message'	=>	'很抱歉、请提供订单编号！'
    			]);
    		}
    		// 查询订单
    		$trade = Db::table('trade')->alias('t')
    			->field('tid AS id, t.type, t.status, t.secret, owner, target, number, price, charge, t.create_at, t.update_at AS date')
    			->where('tid', '=', $id)->find();
    		if (empty($trade)) {
    			return json([
    				'code'		=>	503,
    				'message'	=>	'很抱歉、该订单不存在！',
    			]);
    		}
            // 查询发布者
            $owner = Db::table('profile')->field('username, nickname, avatar, wechat, qq, alipay, realname, idcard, bankname, bankcard, bankaddress')->where('username', '=', $trade['owner'])->find();
            $owner['avatar'] = avatar($owner['avatar'], $owner['idcard']);
            unset($owner['idcard']);
            if ($trade['type'] == self::TYPE_BUY) {
                $ownerWallet = (new Wallet())->get($owner['username']);
                if ($ownerWallet['rmb'] >= $trade['number'] * $trade['price']) {
                    $owner['deliver'] = true;
                }
            }
            // 查询接单者
            $target = [];
            if (!empty($trade['target'])) {
                $target = Db::table('profile')->field('username, nickname, avatar, wechat, qq, alipay, realname, idcard, bankname, bankcard, bankaddress')->where('username', '=', $trade['target'])->find();
                $target['avatar'] = avatar($target['avatar'], $target['idcard']);
                unset($target['idcard']);
            } else {
                // 没有人接单，那么要清空发布者的信息
                $owner['qq'] = '';
                $owner['alipay'] = '';
                $owner['realname'] = '';
                $owner['bankname'] = '';
                $owner['bankcard'] = '';
                $owner['bankaddress'] = '';
                $owner['wechat'] = '';
                $owner['username'] = '***';
            }
            // 身份和角色
            $role = [];
            // 非访客模式
            if (empty($guest)) {
    		      // 我的身份
        		if ($trade['owner'] == $username) {
        			$role[] = 'owner';
        		} else if ($trade['target'] == $username) {
        			$role[] = 'target';
        		} else if ($trade['owner'] != $username && $trade['target'] != $username && empty($trade['target']) && $trade['status'] == 1) {
        			$role[] = 'guest';
        		} else {
        			return json([
        				'code'		=>	504,
        				'message'	=>	'很抱歉、只能查看自己的订单！'
        			]);
        		}
        		// 我的角色
        		if (
        			($trade['type'] == 1 && $trade['owner'] == $username)
        			||
        			($trade['type'] == 2 && $trade['owner'] != $username)
        		) {
        			$role[] = 'buyer';
        		} else {
        			$role[] = 'seller';
        		}
            }
    		// 查询记录
    		$logs = Db::table('trade_log')->where('tid', '=', $id)->where('secret', '=', $trade['secret'])->order('create_at ASC')->select();
    		// 返回结果
            $data = [
                'role'  =>  $role,
                'trade' =>  $trade,
                'logs'  =>  $logs,
                'owner'     =>  $owner,
                'target'    =>  $target,
            ];
            if (empty($guest)) {
                $data['profile'] = $user['profile'];
                $data['avatar'] = avatar($user['profile']['avatar'], $user['profile']['idcard']);
                unset($data['profile']['authen_reason'], $data['profile']['certificate'], $data['profile']['city'], $data['profile']['county'], $data['profile']['idcard'], $data['profile']['province']);
            }
    		return json([
    			'code'		=>	200,
    			'message'	=>	'恭喜您、操作成功！',
    			'data'		=>	$data
    		]);
    	}
    	return $this->fetch();
    }

    /**
     * 交易动作
     */
    public function action(Request $req)
    {
    	try {
    		// 开启事务
    		Db::startTrans();
			// 用户账号
			$username = session('user.account.username');
            // 订单编号
            $id = $req->param('id');
            if (empty($id)) {
                return json([
                    'code'      =>  501,
                    'message'   =>  '很抱歉、请提供订单编号！'
                ]);
            }
            // 操作命令
            $command = $req->param('command/d');
            if (empty($command) && $command != 0) {
                return json([
                    'code'      =>  502,
                    'message'   =>  '很抱歉、错误的操作类型！'
                ]);
            }
            // 安全密码
            $safeword = $req->param('safeword');
			// 用户对象
			$ac = new Account();
			// 用户账号
            if ($command == self::COMMAND_SUCCESS) {
                $user = $ac->instance($username, null, $safeword, true);
                if (empty($user)) {
                    return json([
                        'code'      =>  503,
                        'message'   =>  '很抱歉、安全密码不正确！'
                    ]);
                }
            } else {
                $user = $ac->instance($username, null, null, true);
    			if (empty($user)) {
    				return json([
    					'code'		=>	503,
    					'message'	=>	'很抱歉、请重新登录！'
    				]);
    			}
            }
			if (empty($user['account']['status'])) {
				return json([
					'code'		=>	504,
					'message'	=>	'很抱歉、您的账号已被冻结！'
				]);
			}
	    	if ($user['account']['authen'] != 1) {
	    		return json([
	    			'code'		=>	505,
	    			'message'	=>	'很抱歉、您需要先完成实名认证！'
	    		]);
	    	}
			// 查询订单
			$trade = Db::table('trade')->where('tid', '=', $id)->find();
			if (empty($trade)) {
				return json([
					'code'		=>	506,
					'message'	=>	'很抱歉、该订单不存在！'
				]);
			}

			// 要回复的数据
			$data = [];
			// 命令分发
			switch ($command) {
				// 取消订单
				case 0:
					$this->cancel($trade, $user);
					break;
				// 接单
				case 1:
					$data['status'] = $this->receive($trade, $user);
					break;
				// 付款
				case 2:
					$this->payment($trade, $user);
					break;
				// 催付，未到账，请注意
				case 3:
					$this->urge($trade, $user);
					break;
				// 投诉举报
				case 4:
					$this->report($trade, $user);
					break;
				// 双方举证
				case 5:
					// 获取图片
					$imageFile = $req->file('image');
					if (empty($imageFile)) {
						return json([
							'code'		=>	507,
							'message'	=>	'很抱歉、请提供图片文件！'
						]);
					}
					// 上传图片
					$info = $imageFile->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
					if (!$info) {
						return json([
							'code'		=>	508,
							'message'	=>	$imageFile->getError()
						]);
					}
					// 保存路径
					$content = '/upload/' . str_ireplace('\\', '/', $info->getSaveName());
					$data['image'] = $content;
					// 继续处理
					$this->proof($trade, $user, $content);
					break;
				// 退出交易
				case 6:
					$this->quit($trade, $user);
					break;
				// 交易成功
				case 8:
					$this->confirm($trade, $user);
					break;
				// 其他操作
				default:
					return json([
						'code'		=>	509,
						'message'	=>	'很抱歉、错误的操作类型！'
					]);
					break;
			}
            // 操作日志
            $this->log(68, $command, $username);
    		// 提交事务
    		Db::commit();
    	} catch (\Exception $e) {
    		Db::rollback();
    		return json([
    			'code'		=>	530,
    			'message'	=>	$e->getMessage(),
    			'trace'		=>	$e->getTrace(),
    		]);
    	}
		// 返回结果
		return json([
			'code'		=>	200,
			'message'	=>	'恭喜您、操作成功！',
			'data'		=>	$data
		]);
    }

    /**
     * 系统提示
     */
    public function system(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 判断身份
            if (!session('?staff')) {
                return json([
                    'code'      =>  501,
                    'message'   =>  '很抱歉、您没有权限执行该操作！'
                ]);
            }
            // 订单编号
            $id = $req->param('id');
            if (empty($id)) {
                return json([
                    'code'      =>  504,
                    'message'   =>  '很抱歉、请提供订单编号！'
                ]);
            }
            // 查询订单
            $trade = Db::table('trade')->where('tid', '=', $id)->find();
            if (empty($trade)) {
                return json([
                    'code'      =>  505,
                    'message'   =>  '很抱歉、该订单不存在！'
                ]);
            }
            // 获取数据
            $content = $req->param('content');
            if (empty($content)) {
                return json([
                    'code'      =>  506,
                    'message'   =>  '很抱歉、内容不能为空！'
                ]);
            }
            // 要回复的数据
            $data = [];
            // 添加交易记录
            $bool = Db::table('trade_log')->insert([
                'tid'       =>  $trade['tid'],
                'secret'    =>  $trade['secret'],
                'command'   =>  self::COMMAND_SYSTEM,
                'username'  =>  '00000000000',
                'content'   =>  $content,
                'create_at' =>  $this->timestamp
            ]);
            if (empty($bool)) {
                throw new \Exception("很抱歉、更新交易记录失败！");
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  530,
                'message'   =>  $e->getMessage(),
                'trace'     =>  $e->getTrace(),
            ]);
        }
        // 返回结果
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
            'data'      =>  $data
        ]);
    }

    /**
     * 系统审判
     */
    public function trial(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 判断身份
            if (!session('?staff')) {
                return json([
                    'code'      =>  501,
                    'message'   =>  '很抱歉、您没有权限执行该操作！'
                ]);
            }
            // 订单编号
            $id = $req->param('id');
            if (empty($id)) {
                return json([
                    'code'      =>  504,
                    'message'   =>  '很抱歉、请提供订单编号！'
                ]);
            }
            // 查询订单
            $trade = Db::table('trade')->where('tid', '=', $id)->where('status', '=', self::STATUS_IN_THE_COMPLAINT)->find();
            if (empty($trade)) {
                return json([
                    'code'      =>  505,
                    'message'   =>  '很抱歉、该订单不存在！'
                ]);
            }
            // 获取胜利角色
            $role = $req->param('role/d');
            if (empty($role) || !in_array($role, [1, 2])) {
                return json([
                    'code'      =>  506,
                    'message'   =>  '很抱歉、错误的角色！'
                ]);
            }
            // 根据角色判断胜利，1：买方胜利，2：卖方胜利
            if ($role == 1) {
                // 找到卖家
                $seller_username = ($trade['type'] == self::TYPE_BUY ? $trade['target'] : $trade['owner']);
                $seller = (new Account())->instance($seller_username);
                // 买方胜利、卖家的钱转给买方，相当于执行确认收货
                $this->confirm($trade, $seller, true);
            } else {
                // 卖方胜利
                if ($trade['type'] == self::TYPE_BUY) {
                    // 找到卖家
                    $seller_username = $trade['target'];
                    $seller = (new Account())->instance($seller_username);
                    // 如果是买入订单，卖方退出交易
                    $this->quit($trade, $seller, true);
                } else {
                    // 找到买家
                    $buyer_username = $trade['target'];
                    $buyer = (new Account())->instance($buyer_username);
                    // 如果是卖出订单，买方退出交易
                    $this->quit($trade, $buyer, true);
                }
            }
            // 要回复的数据
            $data = [];
            // 添加交易记录
            $bool = Db::table('trade_log')->insert([
                'tid'       =>  $trade['tid'],
                'secret'    =>  $trade['secret'],
                'command'   =>  ($role == 1 ? self::COMMAND_BUYER_WIN : self::COMMAND_SELLER_WIN),
                'username'  =>  '00000000000',
                'content'   =>  null,
                'create_at' =>  $this->timestamp
            ]);
            if (empty($bool)) {
                throw new \Exception("很抱歉、更新交易记录失败！");
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code'      =>  530,
                'message'   =>  $e->getMessage(),
                'trace'     =>  $e->getTrace(),
            ]);
        }
        // 返回结果
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
            'data'      =>  $data
        ]);
    }

    /**
     * 数据同步
     */
    public function sync(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 自动取消
            $auto_cancel = config('hello.trade.auto_cancel');
            if (!empty($auto_cancel)) {
                // 过期时间
                $expire_at = date('Y-m-d H:i:s', time() - $auto_cancel);
                // 查询订单
                $trades = Db::table('trade')->where('create_at', '<=', $expire_at)->where('status', '=', self::STATUS_NORMAL)->select();
                // 取消订单
                foreach ($trades as $key => $trade) {
                    // 执行取消
                    $this->cancel($trade, null, true);
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
        // 返回结果
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！'
        ]);
    }
}
