<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use think\facade\Env;
use app\index\controller\Configure;

class Contract extends Base
{
	/**
	 * 商品管理
	 */
	public function index(Request $req)
	{
	    // 提交请求
	    if ($req->isPost()) {
	        // 获取动作
	        $action = $req->param('action');
	        // 合约对象
	        $ct = new \app\index\controller\Contract();
	        // 获取数据
	        if ($action == 'get') {
	            $id = $req->param('id/d');
	            if (empty($id)) {
	                return json([
	                    'code'      =>  501,
	                    'message'   =>  '很抱歉、请提供编号！'
	                ]);
	            }
	            try {
	                $obj = $ct->get($id);
	                if (!empty($obj['source'])) {
	                    $obj['source'] = json_decode($obj['source'], true);
	                }
	            } catch (\Exception $e) {
	                return json([
	                    'code'      =>  502,
	                    'message'   =>  '很抱歉、该商品不存在！'
	                ]);
	            }
	            return json([
	                'code'      =>  200,
	                'message'   =>  '恭喜您、操作成功！',
	                'data'      =>  $obj
	            ]);
	        }
	    }
	    // 查询对象
	    $query = Db::table('contract');
	    // 搜索数据
	    $logs = $query->order('loop DESC, update_at DESC')->where('delivery', '=', 1)->paginate(20, false, ['query' => $req->param()]);
	    $this->assign('logs', $logs);
	    // 显示页面
	    return $this->fetch();
	}

	/**
	 * 添加商品
	 */
	public function create(Request $req)
	{
		// 合约对象
	    $ct = new \app\index\controller\Contract();
		// 商品名称
		$title = $req->param('title');
		if (empty($title)) {
		    $this->error('很抱歉、请提供商品名称！');
		    exit;
		}
		// 基础价格
		$base_price = $req->param('base_price');
		if (empty($base_price)) {
		    $this->error('很抱歉、请提供基础价格！');
		    exit;
		}
		// 商品分类
		$catalog = $req->param('catalog/d') ?: 0;
		// 增幅比例
		$inc = $req->param('inc/f') ?: 0;
		// 手续费比例
		$charge = $req->param('charge/f') ?: 0;
		// 每秒收益
		$profit = $req->param('profit/f') ?: 0;
		// 可否交割
		$is_delivery = $req->param('is_delivery/d') ?: 0;
		// 商品详情
		$content = $req->param('content');
		// 图片地址
		$file = $req->file('image');
		if (empty($file)) {
		    $this->error('很抱歉、请提供商品图片！');
		    exit;
		} else {
		    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
		    if (!$info) {
		        $this->error('很抱歉、' . $file->getError() . '！');
		        exit;
		    }
		    $image = $info->getSaveName();
		}
		// 宝贝详情
		$content = $req->param('content');
		try {
		    $data = [
		    	'audit'		=>	1,
		        'catalog'   =>  $catalog,
		        'title'     =>  $title,
		        'base_price'=>  $base_price,
		        'inc'       =>  $inc,
		        'charge'    =>  $charge,
		        'profit'    =>  $profit,
		        'is_delivery'    =>  $is_delivery,
		        'content'   =>  $content,
		    ];
		    if (!empty($image)) {
		        $data['image'] = $image;
		    }
	        // 创建数据
	        $ct->create($data);
		} catch (\Exception $e) {
		    $this->error($e->getMessage());
		    exit;
		}
		// 操作成功
		$this->success('恭喜您、操作成功！');
		exit;
	}

	/**
	 * 编辑商品
	 */
	public function edit(Request $req)
	{
		// 合约对象
	    $ct = new \app\index\controller\Contract();
		// 商品编号
        $id = $req->param('id/d');
        if (empty($id)) {
            $this->error('很抱歉、请刷新页面再试一次！');
            exit;
        }
		// 商品名称
		$title = $req->param('title');
		if (empty($title)) {
		    $this->error('很抱歉、请提供商品名称！');
		    exit;
		}
		// 基础价格
		$base_price = $req->param('base_price');
		if (empty($base_price)) {
		    $this->error('很抱歉、请提供基础价格！');
		    exit;
		}
		// 商品分类
		$catalog = $req->param('catalog/d') ?: 0;
		// 增幅比例
		$inc = $req->param('inc/f') ?: 0;
		// 手续费比例
		$charge = $req->param('charge/f') ?: 0;
		// 每秒收益
		$profit = $req->param('profit/f') ?: 0;
		// 可否交割
		$is_delivery = $req->param('is_delivery/d') ?: 0;
		// 商品详情
		$content = $req->param('content');
		// 图片地址
		$file = $req->file('image');
		if (empty($file)) {
		    $image = null;
		} else {
		    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
		    if (!$info) {
		        $this->error('很抱歉、' . $file->getError() . '！');
		        exit;
		    }
		    $image = $info->getSaveName();
		}
		// 宝贝详情
		$content = $req->param('content');
		try {
		    $data = [
		        'catalog'   =>  $catalog,
		        'title'     =>  $title,
		        'base_price'=>  $base_price,
		        'inc'       =>  $inc,
		        'charge'    =>  $charge,
		        'profit'    =>  $profit,
		        'is_delivery'    =>  $is_delivery,
		        'content'   =>  $content,
		    ];
		    if (!empty($image)) {
		        $data['image'] = $image;
		    }
	        // 排列顺序
            $data['sort'] = $req->param('sort/d') ?: 0;
            // 是否可见
            $data['visible'] = $req->param('visible/d') ?: 0;
            // 审核通过
            $data['audit'] = $req->param('audit/d') ?: 0;
            /*// 代理商
            $data['agent'] = $req->param('agent') ?: null;
            // 归属人
            $data['owner'] = $req->param('owner') ?: null;*/
            // 当前价格
            $data['now_price'] = $req->param('now_price') ?: 0;
            // 更新数据
            $ct->update($id, $data);
		} catch (\Exception $e) {
		    $this->error($e->getMessage());
		    exit;
		}
		// 操作成功
		$this->success('恭喜您、操作成功！');
		exit;
	}

	/**
	 * 编辑审核
	 */
	public function audit(Request $req)
	{
	    // 提交请求
	    if ($req->isPost()) {
	        try {
	            // 获取动作
	            $action = $req->param('action');
	            // 合约对象
	            $ct = new \app\index\controller\Contract();
	            // 获取编号
	            $id = $req->param('id/d');
	            if (empty($id)) {
	                throw new \think\Exception("很抱歉、请提供编号！");
	            }
	            // 查询商品
	            $obj = $ct->get($id);
	            // 通过
	            if ($action == 'agree') {
	                // 新的数据
	                $source = json_decode($obj['source'], true);
	                // 更改数据
	                $data = [];
	                $data['title'] = $source['title'];
	                $data['is_delivery'] = $source['is_delivery'];
	                if (array_key_exists('image', $source)) {
	                    $data['image'] = $source['image'];
	                }
	                $data['content'] = $source['content'];
	                $data['source'] = null;
	                // 更改数据
	                $ct->update($id, $data);
	            } else {
	                // 更改数据
	                $data['source'] = null;
	                $ct->update($id, $data);
	            }
	        } catch (\Exception $e) {
	            $this->error($e->getMessage());
	            exit;
	        }
	        // 操作成功
	        $this->success('恭喜您、操作成功！');
	        exit;
	    }
	    // 查询对象
	    $query = Db::table('contract');
	    // 搜索数据
	    $logs = $query->where('source', 'not null')->order('loop DESC, update_at DESC')->paginate(20, false, ['query' => $req->param()]);
	    $this->assign('logs', $logs);
	    // 显示页面
	    return $this->fetch();
	}

	/**
	 * 商品交割
	 */
	public function delivery(Request $req)
	{
	    // 提交请求
	    if ($req->isPost()) {
	        // 获取动作
	        $action = $req->param('action');
	        // 合约对象
	        $ct = new \app\index\controller\Contract();
	        // 获取数据
	        if ($action == 'get') {
	            $id = $req->param('id/d');
	            if (empty($id)) {
	                return json([
	                    'code'      =>  501,
	                    'message'   =>  '很抱歉、请提供编号！'
	                ]);
	            }
	            try {
	                $obj = $ct->get($id);
	                if (!empty($obj['source'])) {
	                    $obj['source'] = json_decode($obj['source'], true);
	                }
	            } catch (\Exception $e) {
	                return json([
	                    'code'      =>  502,
	                    'message'   =>  '很抱歉、该商品不存在！'
	                ]);
	            }
	            return json([
	                'code'      =>  200,
	                'message'   =>  '恭喜您、操作成功！',
	                'data'      =>  $obj
	            ]);
	        }
	        // 商品编号
	        $id = $req->param('id/d');
	        if ($action == 'update' && empty($id)) {
	            $this->error('很抱歉、请刷新页面再试一次！');
	            exit;
	        }
	        // 商品名称
	        $title = $req->param('title');
	        if (empty($title)) {
	            $this->error('很抱歉、请提供商品名称！');
	            exit;
	        }
	        // 基础价格
	        $base_price = $req->param('base_price');
	        if (empty($base_price)) {
	            $this->error('很抱歉、请提供基础价格！');
	            exit;
	        }
	        // 商品分类
	        $catalog = $req->param('catalog/d') ?: 0;
	        // 增幅比例
	        $inc = $req->param('inc/f') ?: 0;
	        // 手续费比例
	        $charge = $req->param('charge/f') ?: 0;
	        // 每秒收益
	        $profit = $req->param('profit/f') ?: 0;
	        // 可否交割
	        $is_delivery = $req->param('is_delivery/d') ?: 0;
	        // 商品详情
	        $content = $req->param('content');
	        // 图片地址
	        $file = $req->file('image');
	        if (empty($file)) {
	            $image = null;
	        } else {
	            $info = $file->validate(['ext' => 'jpg,jpeg,png'])->move(Env::get('root_path') . 'public/upload');
	            if (!$info) {
	                $this->error('很抱歉、' . $file->getError() . '！');
	                exit;
	            }
	            $image = $info->getSaveName();
	        }
	        // 宝贝详情
	        $content = $req->param('content');
	        try {
	            $data = [
	                'catalog'   =>  $catalog,
	                'title'     =>  $title,
	                'base_price'=>  $base_price,
	                'inc'       =>  $inc,
	                'charge'    =>  $charge,
	                'profit'    =>  $profit,
	                'is_delivery'    =>  $is_delivery,
	                'content'   =>  $content,
	            ];
	            if (!empty($image)) {
	                $data['image'] = $image;
	            }
	            // 添加宝贝
	            if ($action == 'create') {
	                // 创建数据
	                $ct->create($data);
	            } else {
	                // 排列顺序
	                $data['sort'] = $req->param('sort/d') ?: 0;
	                // 是否可见
	                $data['visible'] = $req->param('visible/d') ?: 0;
	                // 审核通过
	                $data['audit'] = $req->param('audit/d') ?: 0;
	                /*// 代理商
	                $data['agent'] = $req->param('agent') ?: null;
	                // 归属人
	                $data['owner'] = $req->param('owner') ?: null;*/
	                // 当前价格
	                $data['now_price'] = $req->param('now_price') ?: 0;
	                // 更新数据
	                $ct->update($id, $data);
	            }
	        } catch (\Exception $e) {
	            $this->error($e->getMessage());
	            exit;
	        }
	        // 操作成功
	        $this->success('恭喜您、操作成功！');
	        exit;
	    }
	    // 查询对象
	    $query = Db::table('contract')->where('delivery', '<>', 1);
	    // 搜索数据
	    $logs = $query->order('loop DESC, update_at DESC')->paginate(20, false, ['query' => $req->param()]);
	    $this->assign('logs', $logs);
	    // 显示页面
	    return $this->fetch();
	}

	/**
	 * 代理商
	 */
	public function agent(Request $req)
	{
	    // 获取配置
	    $contract = Configure::get('hello.contract') ?: [];
	    // 代理人列表
	    $agent = array_key_exists('agent', $contract) ? $contract['agent'] : [];
	    // 删除代理人
	    $remove = $req->param('remove');
	    if (!empty($remove) && array_key_exists($remove, $agent)) {
	        unset($agent[$remove]);
	        // 更新配置
	        Configure::set('hello.contract', [
	            'agent' =>  $agent
	        ]);
	    }
	    // 添加代理人
	    if ($req->isPost()) {
	        // 获取手机
	        $username = $req->param('username');
	        if (empty($username)) {
	            $this->error('很抱歉、请填写手机号码！');
	            exit;
	        }
	        // 获取比例
	        $ratio = $req->param('ratio');
	        if (empty($ratio) || $ratio < 0) {
	            $this->error('很抱歉、错误的比例！');
	            exit;
	        }
	        // 获取省市区
	        $province = $req->param('province');
	        $city = $req->param('city');
	        $county = $req->param('county');
	        $address = $county ?: $city;
	        $address = $address ?: $province;
	        // 获取等级
	        $level = $req->param('level');
	        // 保存设置
	        $agent[$username] = [
	            'username'      =>  $username,
	            'level'         =>  $level,
	            'ratio'         =>  $ratio,
	            'address'       =>  $address
	        ];
	        // 数组排序
	        uasort($agent, function($a, $b){
	            if ($a['address'] == $b['address']) {
	                return 0;
	            }
	            return $a['address'] < $b['address'] ? -1 : 1;
	        });
	        // 更新配置
	        Configure::set('hello.contract', [
	            'agent' =>  $agent
	        ]);
	        // 操作成功
	        $this->success('恭喜您、操作成功！');
	        exit;
	    }
	    // 循环处理数据
	    foreach ($agent as $key => $item) {
	        // 查询地区
	        $item['address'] = Db::table('region')->where('code', '=', $item['address'])->value('address');
	        // 查询收益
	        $item['income'] = Db::table('record')->where('username', '=', $item['username'])->where('business', '=', 39)->sum('now') ?: 0;
	        // 保存信息
	        $agent[$key] = $item;
	    }
	    // 显示页面
	    $this->assign('agent', $agent);
	    $this->assign('levels', [
	    	1 		=>	'V1普通代理',
	    	2 		=>	'V2黄金代理',
	    	3 		=>	'V3白金代理',
	    	4 		=>	'V4铂金代理',
	    	5 		=>	'V5钻石代理',
	    ]);
	    return $this->fetch();
	}
	/**
	 * 链上合约 - 记录
	 */
	public function logs(Request $req)
	{
	    // 商品列表
	    $cids = Db::table('contract')->field('id, title')->order('sort DESC, loop DESC')->select();
	    $this->assign('cids', $cids);
	    // 查询对象
	    $query = Db::table('contract_log')->alias('l')->join('contract c', 'c.id = l.cid')
	                    ->field('c.title, l.*');
	    // 条件：按账号搜索
	    $username = $req->param('username');
	    if (!is_null($username) && strlen($username)) {
	        $query->where('l.username', '=', $username);
	    }
	    // 条件：按操作类型搜索
	    $action = $req->param('action');
	    if (!is_null($action) && strlen($action)) {
	        $query->where('l.action', '=', $action);
	    }
	    // 条件：按具体商品搜索
	    $cid = $req->param('cid');
	    if (!is_null($cid) && strlen($cid)) {
	        $query->where('l.cid', '=', $cid);
	    }
	    // 搜索数据
	    $logs = $query->where('l.token', 'null')->order('l.create_at DESC')->paginate(20, false, ['query' => $req->param()]);
	    $this->assign('logs', $logs);
	    // 显示页面
	    $this->assign('actions', [
	        1   =>  '一口价',
	        2   =>  '团购',
	        3   =>  '转让',
	        4   =>  '继承',
	        5   =>  '出售',
	    ]);
	    return $this->fetch();
	}

	/**
     * 交割详情
     */
    public function transaction(Request $req)
    {
		// 合约对象
		$ct = new \app\index\controller\Contract();
        // 获取编号
        $id = $req->param('id/d');
        if (empty($id)) {
            throw new \think\Exception("很抱歉、请提供商品编号！");
        }
        try {
            // 查询商品
            $contract = $ct->get($id);
            $this->assign('contract', $contract);
		} catch (\Exception $e) {
            $this->error($e->getMessage());
            exit;
        }
        // 执行修改
        $upd = false;
        $cData = [];
        $lData = [];
        // 取消订单
        if ($req->param('action') == 'cancel') {
        	$upd = true;
        	$cData = [
        		'token'		=>	null,
        		'delivery'	=>	1,
        		'update_at' =>  $this->timestamp,
        	];
        	$lData = [
        		'token'		=>	$contract['token'],
        		'action'	=>	0,
        	];
        }
        // 更改状态
        $status = $req->param('status');
        if ($req->param('action') == 'status') {
        	$upd = true;
        	$cData = [
        		'token'		=>	$contract['token'],
        		'delivery'	=>	$status,
        		'update_at' =>  $this->timestamp,
        	];
        	if ($status == 1) {
        		$cData['token'] = null;
        		$cData['owner'] = null;
        	}
        	$lData = null;
        }
        // 强行取消
        if (!empty($upd)) {
        	try {
        		// 开启事务
        		Db::startTrans();
        		// 交易成功、给代理商钱
        		if ($req->param('action') == 'status' && $status == \app\index\controller\Contract::COMMAND_SUCCESS && !empty($contract['agent'])) {
        		    // 资料代理商
        		    $agent = (new \app\index\controller\Account())->instance($contract['agent']);
        		    // 给代理商钱
        		    (new \app\index\controller\Wallet())->change($contract['agent'], 43, [
        		        1       =>  [
        		            $agent['wallet']['money'],
        		            $contract['base_price'],
        		            $agent['wallet']['money'] + $contract['base_price'],
        		        ],
        		    ]);
        		}
        		// 更新合约
        		$bool = Db::table('contract')->where('id', '=', $contract['id'])->update($cData);
        		if (empty($bool)) {
        		    throw new \think\Exception("很抱歉、更新合约状态失败！");
        		}
        		// 添加记录
        		if (!is_null($lData)) {
	        		$bool = Db::table('contract_log')->insert(array_merge([
	        		    'cid'       =>  $contract['id'],
	        		    'token'		=>	$contract['token'],
	        		    'username'  =>  '00000000000',
	        		    'action'    =>  0,
	        		    'loop'      =>  $contract['loop'],
	        		    'money'     =>  0,
	        		    'charge'    =>  0,
	        		    'target'    =>  null,
	        		    'ratio'     =>  0,
	        		    'content'   =>  null,
	        		    'remark'    =>  null,
	        		    'create_at' =>  $this->timestamp,
	        		], $lData));
	        		if (empty($bool)) {
	        		    throw new \think\Exception("很抱歉、交割记录保存失败！");
	        		}
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

        // 显示页面
        return $this->fetch();
    }
}