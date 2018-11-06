<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use app\index\controller\Configure;

class Calendar extends Base
{

    /**
     * 动作类型
     */
    const ACTION_CLOCK = 1;


    /**
     * 日历首页
     */
    public function index(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 最后签到
        $log = Db::table('calendar_log')->where('username', '=', $username)->where('action', '=', self::ACTION_CLOCK)->order('create_at DESC')->find();
        if (!empty($log)) {
            if ($log['create_at'] >= date('Y-m-d')) {
                $this->assign('today', 'ok');
                if ($log['continuity'] > 0) {
                    $this->assign('continuity', $log['continuity']);
                }
            }
            $this->assign('last_at', $log['create_at']);
        }
        // 获取配置
        $config = Configure::get('hello.calendar');
        $config = empty($config) ? [] : $config;
        $this->assign('config', $config);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 签到打卡
     */
    public function clock(Request $req)
    {
        try {
            // 开启事务
            Db::startTrans();
            // 获取配置
            $config = Configure::get('hello.calendar');
            $config = empty($config) ? [] : $config;
            if (empty($config) || empty($config['enable'])) {
                throw new \think\Exception("很抱歉、系统尚未开启该模块！");
            }
            // 用户账号
            $username = session('user.account.username');
            // 用户对象
            $ac = new Account();
            // 查询账号
            $user = $ac->instance($username);
            if (empty($user)) {
                throw new \think\Exception("很抱歉、安全密码不正确！");
            }
            if (empty($user['account']['status'])) {
                throw new \think\Exception("很抱歉、您的账号已被冻结！");
            }
            if ($user['account']['authen'] != 1) {
                throw new \think\Exception("很抱歉、请先完成实名认证！");
            }

            // 查询今天打卡记录
            $log = Db::table('calendar_log')->where('username', '=', $username)->where('action', '=', self::ACTION_CLOCK)->where('create_at', '>=', date('Y-m-d'))->find();
            if (!empty($log)) {
                throw new \think\Exception("很抱歉、您今天已经签到了！");
            }
            // 查询昨天的打卡记录
            $yesterday = date('Y-m-d', strtotime("-1 day"));
            $yes = Db::table('calendar_log')->where('username', '=', $username)->where('action', '=', self::ACTION_CLOCK)->where('create_at', '>=', $yesterday)->where('create_at', '<', date('Y-m-d'))->find();
            $continuity = 0;
            if (!empty($yes)) {
                $continuity = $yes['continuity'] + 1;
            }
            // 奖励算力
            $power = 0;
            if (array_key_exists('power', $config['default'])) {
                if (is_array($config['default']['power'])) {
                    // 随机奖励
                } else if ($config['default']['power'] > 0) {
                    // 固定奖励
                    $power = $config['default']['power'];
                } else {

                }
            }
            // 添加算力
            if ($power > 0) {
                $ac->dashboard($username, [
                    'power'     =>  Db::raw('power+' . $power),
                ]);
            }
            // 执行打卡
            $data = [
                'username'      =>  $username,
                'action'        =>  self::ACTION_CLOCK,
                'currency'      =>  1,
                'money'         =>  0,
                'machine'       =>  0,
                'power'         =>  $power,
                'continuity'    =>  $continuity,
                'create_at'     =>  $this->timestamp,
            ];
            $bool = Db::table('calendar_log')->insert($data);
            if (empty($bool)) {
                throw new \think\Exception("很抱歉、签到失败请重试！");
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
        // 操作成功
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
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
        $query = Db::table('calendar_log')->field('action, currency, money, power, machine, create_at AS date')->where('username', '=', $username);
        // 查询数据
        $data = $query->limit($offset, $size)->order('create_at DESC')->select();
        foreach ($data as $key => $item) {
            if ($item['action'] == self::ACTION_CLOCK) {
                $item['action'] = '签到';
            }
            $data[$key] = $item;
        }
        // 返回数据
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
            'data'      =>  $data
        ]);
    }

    /**
     * 每月记录
     */
    public function month(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 获取时间
        $date = $req->param('date');
        if (empty($date) || strlen($date) != 10) {
            $date = date('Y-m-d');
        }
        // 开始时间
        $start = substr($date, 0, 8);
        $start .= '01';
        // 结束时间
        $end = date('Y-m', strtotime("+1 months", strtotime($start)));
        $end .= '-01';
        // 查询记录
        $data = Db::table('calendar_log')->fieldRaw("DATE_FORMAT(create_at, '%Y-%m-%d') AS day")
                ->where('action', '=', self::ACTION_CLOCK)
                ->where('create_at', '>=', $start)
                ->where('create_at', '<', $end)
                ->where('username', '=', $username)
                ->group('day')->select();
        // 返回数据
        return json([
            'code'      =>  200,
            'message'   =>  '恭喜您、操作成功！',
            'data'      =>  $data
        ]);
    }
}
