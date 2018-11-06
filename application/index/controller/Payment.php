<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use think\facade\Env;
use Payment\Client\Charge;
use Payment\Client\Notify;
use Payment\Notify\AliNotify;
use Payment\Common\PayException;
use Payment\Config as PaymentConfig;
use Payment\Notify\PayNotifyInterface;

class Payment extends Base
{
    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------

    /**
     * 生成PID
     */
    public function generatePID()
    {
        $prefix = chr(mt_rand(65, 90));
        do {
            $number = mt_rand(100000000, 999999999);
            $mid = $prefix . $number;
        } while (!empty(Db::table('uuid')->where('id', '=', $mid)->find()));
        $bool = Db::table('uuid')->insert(['id' => $mid, 'type' => 20]);
        if (empty($bool)) {
            throw new \think\Exception("很抱歉、充值编号生成失败！");
        }
        return $mid;
    }

    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

    /**
     * 充值首页
     */
    public function index(Request $req)
    {
        exit;
        if ($req->param('null')) {
            cache('notify', null);
            exit;
        }
        if ($req->param('debug')) {
            $data = cache('notify');
            if (!empty($data)) {
                foreach ($data as $time => $item) {
                    echo $item['date'] . '&nbsp;&nbsp;&nbsp;' . $item['channel'] . '<br />';
                    var_dump($item);
                    echo '<br /><br />';
                }
            }
            exit;
        }
        // 获取渠道
        $channel = $req->param('channel');
        if (!in_array($channel, ['alipay', 'wechat'])) {
            $this->error('很抱歉、错误的充值方式！');
            exit;
        }
        try {
            // 通过支付宝
            if ($channel == 'alipay') {
                $url = Charge::run(PaymentConfig::ALI_CHANNEL_WEB, config('hello.payment.alipay'), [
                    'subject'   =>  'title',
                    'body'      =>  'body',
                    'order_no'  =>  'ali' . time(),
                    'amount'    =>  0.01,
                ]);
            }
            // 通过微信
            else if ($channel == 'wechat') {
                $url = Charge::run(PaymentConfig::WX_CHANNEL_WAP, config('hello.payment.wechat'), [
                    'subject'   =>  'title',
                    'body'      =>  'body',
                    'order_no'  =>  'wx' . time(),
                    'amount'    =>  0.01,

                    'timeout_express'   =>  time() + 600,// 表示必须 600s 内付款
                    'return_param'      =>  '123',
                    'client_ip'         =>  $req->ip(),

                    //{"h5_info": {"type":"Wap","wap_url": "https://pay.qq.com","wap_name": "腾讯充值"}}
                    'scene_info'        =>  [
                        'type'          =>  'Wap',// IOS  Android  Wap  腾讯建议 IOS  ANDROID 采用app支付
                        'wap_url'       =>  'http://www.fccion.com/',//自己的 wap 地址
                        'wap_name'      =>  '测试充值',
                    ],
                ]);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        // 去付款
        $this->redirect($url);
        exit;
    }

    /**
     * 立即充值
     */
    public function recharge(Request $req)
    {
        try {
            // 充值配置
            $config = Configure::get('hello.payment');
            $config = empty($config) ? [] : $config;
            // 用户账号
            $username = session('user.account.username');
            if (empty($username)) {
                throw new \think\Exception("很抱歉、请重新登录！");
            }
            // 安全密码
            $safeword = $req->param('safeword');
            if (empty($safeword)) {
                throw new \think\Exception("很抱歉、请输入安全密码！");
            }
            // 用户对象
            $user = (new Account())->instance($username, null, $safeword);
            if (empty($user)) {
                throw new \think\Exception("很抱歉、安全密码不正确！");
            }
            // 获取金额
            $money = $req->param('money/f');
            if (empty($money) || $money <= 0) {
                throw new \think\Exception("很抱歉、充值金额不正确！");
            }
            // 判断金额
            if ($money < $config['min'] || $money > $config['max']) {
                throw new \think\Exception("很抱歉、金额必须在" . $config['min'] . "到" . $config['max'] . "之间！");
            }
            // 获取渠道
            $channel = $req->param('channel');
            if (!in_array($channel, ['alipay', 'wechat']) || !array_key_exists($channel, $config)) {
                throw new \think\Exception("很抱歉、错误的充值方式！");
            }
            // 开启事务
            Db::startTrans();
            // 生成订单
            $pid = $this->generatePID();
            $bool = Db::table('payment')->insert([
                'pid'           =>  $pid,
                'token'         =>  null,
                'status'        =>  2,
                'username'      =>  $username,
                'channel'       =>  $channel,
                'currency'      =>  8,
                'number'        =>  $money,
                'charge'        =>  0,
                'notify_at'     =>  null,
                'create_at'     =>  $this->timestamp,
                'update_at'     =>  $this->timestamp,
            ]);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、充值订单创建失败！");
            }
            // APP标题
            $title = config('hello.title');
            // 通过支付宝
            if ($channel == 'alipay') {
                $url = Charge::run(PaymentConfig::ALI_CHANNEL_WAP, $config['alipay'], [
                    'subject'   =>  '翡翠链',
                    'body'      =>  $title,
                    'order_no'  =>  $pid,
                    'amount'    =>  $money,

                    'timeout_express'   =>  time() + 600,// 表示必须 600s 内付款
                    'quit_url'          =>  $config['alipay']['return_url'],
                ]);
            }
            // 通过微信
            if ($channel == 'wechat') {
                $url = Charge::run(PaymentConfig::WX_CHANNEL_WAP, $config['wechat'], [
                    'subject'   =>  '翡翠链',
                    'body'      =>  $title,
                    'order_no'  =>  $pid,
                    'amount'    =>  $money,

                    'timeout_express'   =>  time() + 600,// 表示必须 600s 内付款
                    'client_ip'         =>  $req->ip(),

                    //{"h5_info": {"type":"Wap","wap_url": "https://pay.qq.com","wap_name": "腾讯充值"}}
                    'scene_info'        =>  [
                        'type'          =>  'Wap',// IOS  Android  Wap  腾讯建议 IOS  ANDROID 采用app支付
                        'wap_url'       =>  $config['wechat']['redirect_url'],//自己的 wap 地址
                        'wap_name'      =>  $title,
                    ],
                ]);
            }
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
            'data'      =>  [
                'redirect'  =>  $url,
            ],
        ]);
    }

    /**
     * 异步通知
     */
    public function notify(Request $req, $channel)
    {
        try {
            // 支付类型
            $type = 'ali_charge';
            $config = config('hello.payment.alipay');
            if ($channel == 'wechat') {
                $type = 'wx_charge';
                $config = config('hello.payment.wechat');
            }
            // 原始数据
            $raw = Notify::getNotifyData($type, $config);
            // $nt = cache('notify');
            $data = [
                'channel'   =>  $channel,
                'date'      =>  date('Y-m-d H:i:s'),
                'raw'       =>  $raw,
            ];
            /*if (empty($nt)) {
                $nt = [
                    time()     =>   $data
                ];
            } else {
                $nt[time()] = $data;
            }
            cache('notify', $nt);*/
            // 异步通知
            $ret = Notify::run($type, $config, new Class implements PayNotifyInterface {
                // 异步处理
                public function notifyProcess(array $data)
                {
                    // 查询订单
                    $order = Db::table('payment')->where('pid', '=', $data['order_no'])->find();
                    if (empty($order)) {
                        return false;
                    }
                    // 已经成功
                    if ($order['status'] == 1) {
                        return true;
                    }
                    // 订单数据
                    $date = date('Y-m-d H:i:s');
                    $orderData = [
                        'token'     =>  $data['transaction_id'],
                        'raw'       =>  json_encode($data),
                        'notify_at' =>  $date,
                        'update_at' =>  $date,
                    ];
                    // 充值渠道
                    $channel = $data['channel'];
                    if ($channel === PaymentConfig::ALI_CHARGE) {
                        // 商户订单号：order_no
                        // 外部订单号：transaction_id
                        // 商家实收款：receipt_amount
                        if ($order['number'] == $data['receipt_amount']) {
                            $orderData['status'] = 1;
                        } else {
                            $orderData['status'] = 0;
                        }
                    } elseif ($channel === PaymentConfig::WX_CHARGE) {
                        if ($order['number'] == $data['amount']) {
                            $orderData['status'] = 1;
                        } else {
                            $orderData['status'] = 0;
                        }
                    } elseif ($channel === PaymentConfig::CMB_CHARGE) {
                        // 招商支付
                    } elseif ($channel === PaymentConfig::CMB_BIND) {
                        // 招商签约
                    } else {
                        // 其它类型的通知
                    }
                    // 更新订单
                    Db::table('payment')->where('pid', '=', $data['order_no'])->update($orderData);

                    try {
                        // 开启事务
                        Db::startTrans();

                        // 查询用户
                        $user = (new Account())->instance($order['username']);
                        // 给用户加钱
                        (new Wallet())->change($order['username'], 86, [
                            8       =>  [
                                $user['wallet']['rmb'],
                                $order['number'],
                                $user['wallet']['rmb'] + $order['number'],
                            ]
                        ]);

                        // 提交事务
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        return false;
                    }

                    // 执行业务逻辑，成功后返回true
                    return true;
                }
            });
            echo $ret;
        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }
    }

    /**
     * 页面回调
     */
    public function callback(Request $req)
    {
        $this->success('恭喜您、充值完成！', '/wallet/counter.html?c=8');
        exit;
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
        $query = Db::table('payment')->field('pid AS id, channel, status, number, charge, update_at AS date')->where('username', '=', $username);
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
