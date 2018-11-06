<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Base extends Controller
{

    public $timestamp;

    public $redis;

    public function __construct()
    {
        parent::__construct();
        $this->timestamp = date('Y-m-d H:i:s');
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    private function toast($message = '很抱歉、请重新登录！', $data = [])
    {
        $json = json_encode([
            'code' => 403,
            'message' => $message,
            'data' => $data,
        ]);
        header('Content-Type: application/json; charset=utf-8');
        echo $json;
        exit;
    }

    public function initialize()
    {
        // 请求对象
        $req = request();
        // 来自APP
        $from = $req->param('from');
        if ($from == 'app') {
            // 设置Cookie
            cookie('platform', 'app', 86400 * 7);
            session('platform', 'app');
        }

        // 无需登录的方法
        $except = ['Index/index', 'Account/signin', 'Account/signup', 'Account/check', 'Account/forgot', 'Service/exchange', 'Service/sms', 'Service/sms_check', 'Service/captcha', 'Service/region', 'Oauth/qq_login', 'Oauth/qq_callback', 'Payment/notify', 'Payment/callback', 'Wallet/release', 'Market/sync'];
        // 当前路由
        $current = $req->controller() . '/' . $req->action();
        // 没有Session、需要进行检测
        if (!session('?staff') && !session('?manager') && !session('?user') && !in_array($current, $except)) {
            if ($req->isPost()) {
                $this->toast();
                exit;
            } else {
                header('Location: /signin.html?from=' . urlencode($req->url(true)));
                exit;
            }
        }
        // 后台登录了，前台没登录
        if ($req->module() != 'admin' && !session('?user') && !in_array($current, $except)) {
            if ((session('?staff') || session('?manager')) && $req->isPost()) {
                // 无需做什么
            } else {
                header('Location: /signin.html?from=' . urlencode($req->url(true)));
                exit;
            }
        }
        // 所在平台
        $ua = strtolower($req->header('user-agent'));
        if (stripos($ua, 'iphone') !== false) {
            $this->assign('platform', 'ios');
        } else if (stripos($ua, 'window') !== false) {
            $this->assign('platform', 'pc');
        } else {
            $this->assign('platform', 'android');
        }

        // 操作太快
        if (is_null($this->redis)) {
            $this->redis = new \Redis();
            $this->redis->connect('127.0.0.1', 6379);
        }
        if ($req->isPost() && !in_array($current, $except) && (!session('?staff') && !session('?manager'))) {
            $username = session('user.account.username');
            $className = get_class($this);
            /*if () {
            var_dump([$className, $current]);
            }*/
            $key = $username . ':' . $current;
            $result = $this->redis->get($key);
            if (!empty($result)) {
                if ($result == $className) {
                    $this->toast('很抱歉、您的操作太快了！', [$result]);
                    exit;
                }
            } else {
                $this->redis->set($key, $className, 1);
            }
        }
    }

    public function log($type, $text = null, $username = null)
    {
        $req = request();
        $data = [
            'username' => $username ?: session('user.account.username'),
            'type' => $type,
            'text' => $text,
            'ip' => $req->ip(),
            'ua' => $req->header('user-agent'),
            'create_at' => $this->timestamp,
        ];
        Db::table('log')->insert($data);
    }
}
