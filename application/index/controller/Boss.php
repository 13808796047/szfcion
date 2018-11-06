<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use think\facade\Env;

class Boss extends Base
{

    // +----------------------------------------------------------------------
    // | 私有函数
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 内部方法
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 对外接口
    // +----------------------------------------------------------------------

    /**
     * 老板首页
     */
    public function index(Request $req)
    {
        // 获取配置
        $config = Configure::get('hello.boss');
        $config = $config ?: [];
        $this->assign('config', $config);
        // 用户账号
        $username = session('user.account.username');
        // 老板资料
        $boss = Db::table('boss')->where('username', '=', $username)->find();
        $this->assign('boss', $boss);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 欢迎老板
     */
    public function welcome(Request $req)
    {
        // 获取配置
        $config = Configure::get('hello.boss');
        $config = $config ?: [];
        $this->assign('config', $config);
        // 显示页面
        return $this->fetch();
    }

    /**
     * 搜索数据
     */
    public function search(Request $req)
    {
        // 获取配置
        $config = Configure::get('hello.boss');
        $config = $config ?: [];
        $level = [
            'lv1'   =>  array_key_exists('lv1', $config) ? $config['lv1'] : PHP_INT_MAX,
            'lv2'   =>  array_key_exists('lv2', $config) ? $config['lv2'] : PHP_INT_MAX,
            'lv3'   =>  array_key_exists('lv3', $config) ? $config['lv3'] : PHP_INT_MAX,
        ];
        // 分页数据
        $page = $req->param('page/d', 1);
        $size = $req->param('size/d', 20);
        $offset = $page - 1 < 0 ? 0 : ($page - 1) * $size;
        // 查询对象
        $query = Db::table('boss')->alias('b')
                ->leftJoin('wallet w', 'w.username = b.username')
                ->leftJoin('dashboard d', 'd.username = b.username')
                ->leftJoin('profile p', 'p.username = b.username')
                ->leftJoin('region r', 'r.code = p.county')
                ->field('b.id, p.avatar, p.realname, w.money * d.power AS weight, r.province_name AS region, b.title, b.background, b.advantage');
        // 条件：按类目搜索
        $catalog = $req->param('catalog/d');
        if (!empty($catalog)) {
            $query->where('catalog', '=', $catalog);
        }
        // 查询数据
        $data = $query->where('status', '=', 1)->order('weight DESC, b.create_at DESC')->limit($offset, $size)->select();
        // 整理数据
        foreach ($data as $key => $item) {
            foreach ($level as $_key => $value) {
                if ($item['weight'] > $value) {
                    $item['level'] = $_key;
                }
            }
            $item['avatar'] = avatar($item['avatar']);
            unset($item['weight']);
            $item['background'] = mb_substr($item['background'], 0, 30, 'UTF-8');
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
     * 完善资料
     */
    public function profile(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 获取配置
        $config = Configure::get('hello.boss');
        $level = [
            'lv1'   =>  array_key_exists('lv1', $config) ? $config['lv1'] : PHP_INT_MAX,
            'lv2'   =>  array_key_exists('lv2', $config) ? $config['lv2'] : PHP_INT_MAX,
            'lv3'   =>  array_key_exists('lv3', $config) ? $config['lv3'] : PHP_INT_MAX,
        ];
        // 提交申请
        if ($req->isPost()) {
            try {
                // 未开启
                if (empty($config) || empty($config['enable'])) {
                    throw new \think\Exception("很抱歉、系统暂未开启该模块！");
                }
                // 用户账号
                $user = (new Account())->instance($username);
                if (empty($user['account']['status'])) {
                    throw new \think\Exception("很抱歉、您的账号已被冻结！");
                }
                if ($user['account']['authen'] != 1) {
                    throw new \think\Exception("很抱歉、请先通过实名认证！");
                }
                // 状态不对
                $boss = Db::table('boss')->where('username', '=', $username)->find();
                if (empty($boss) || $boss['status'] != 1) {
                    throw new \think\Exception("很抱歉、您还未通过认证！");
                }
                // 开启事务
                Db::startTrans();

                // 微信账号
                $wechat = $req->param('wechat');
                if (empty($wechat) || strlen($wechat) < 5) {
                    throw new \think\Exception("很抱歉、请填写微信账号！");
                }
                // QQ
                $qq = $req->param('qq');
                if (empty($qq) || strlen($qq) < 5) {
                    throw new \think\Exception("很抱歉、请填写qq号码！");
                }
                /*// 支付宝
                $alipay = $req->param('alipay');
                if (empty($alipay) || strlen($alipay) < 5) {
                    throw new \think\Exception("很抱歉、请填写支付宝账号！");
                }*/
                // 获取地区
                $region = $req->param('region');
                if (empty($region)) {
                    throw new \think\Exception("很抱歉、请选择所在地区！");
                }
                $region = Db::table('region')->where('code', '=', $region)->where('type', '=', 3)->find();
                if (empty($region)) {
                    throw new \think\Exception("很抱歉、错误的地区！");
                }
                // 获取头像
                $avatar = null;
                $file = $req->file('avatar');
                if (!empty($file)) {
                    // 图片检查
                    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->check();
                    if (!$info) {
                        throw new \think\Exception("很抱歉、错误的头像图片！");
                    }
                    // 存放目录
                    $folder = Env::get('root_path') . 'public/avatar/';
                    is_dir($folder . date('Ymd')) or mkdir($folder . date('Ymd'), 0777, true);
                    // 图片压缩
                    $image = \think\Image::open($file);
                    $avatar = '/' . date('Ymd') . '/' . md5(time() . mt_rand()) . '.' . $image->type();
                    $image->thumb(640, 640)->save($folder . $avatar);
                }

                // 保存到个人资料
                $data = [
                    'wechat'        =>  $wechat,
                    'qq'            =>  $qq,
                    // 'alipay'        =>  $alipay,
                    'province'      =>  $region['province'],
                    'city'          =>  $region['city'],
                    'county'        =>  $region['code'],
                    'update_at'     =>  $this->timestamp,
                ];
                if (!empty($avatar)) {
                    $data['avatar'] = $avatar;
                }
                $bool = Db::table('profile')->where('username', '=', $username)->update($data);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、个人资料更新失败！");
                }

                // 获取头衔
                $title = $req->param('title');
                if (empty($title)) {
                    throw new \think\Exception("很抱歉、请选择头衔！");
                }
                $titleArray = explode(',', $title);
                $systemTitle = array_key_exists('title', $config) ? $config['title'] : '';
                foreach ($titleArray as $key => $value) {
                    if (stripos($systemTitle, $value) === false) {
                        throw new \think\Exception("很抱歉、【$value】不存在！");
                    }
                }
                // 获取背景
                $background = $req->param('background');
                if (empty($background)) {
                    throw new \think\Exception("很抱歉、请填写背景介绍！");
                }
                // 获取优势
                $advantage = $req->param('advantage');
                if (empty($advantage)) {
                    throw new \think\Exception("很抱歉、请填写个人优势！");
                }

                // 保存老板资料
                $bool = Db::table('boss')->where('username', '=', $username)->update([
                    'title'         =>  $title,
                    'advantage'     =>  $advantage,
                    'background'    =>  $background,
                    'update_at'     =>  $this->timestamp,
                ]);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、提交失败请重试！");
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
        // 未开启
        if (empty($config) || empty($config['enable'])) {
            $this->error('很抱歉、系统暂未开启该模块！');
            exit;
        }
        // 查询对象
        $query = Db::table('boss');
        // 获取编号
        $id = $req->param('id/d');
        if (!empty($id)) {
            $query->where('id', '=', $id);
        } else {
            $query->where('username', '=', $username);
        }
        // 老板信息
        $boss = $query->find();
        if (empty($boss) || $boss['status'] != 1) {
            $this->error('很抱歉、该账号尚未通过申请！');
            exit;
        }
        $boss['level'] = '';
        // 老板资料
        if ($username == $boss['username']) {
            $user = (new Account())->instance($boss['username'], null, null, true);
        } else {
            $user = (new Account())->instance($boss['username']);
        }
        $this->assign('user', $user);
        // 老板等级
        foreach ($level as $key => $value) {
            if ($user['wallet']['money'] * $user['dashboard']['power'] > $value) {
                $boss['level'] = $key;
            }
        }
        $this->assign('boss', $boss);
        // 查询地区
        $region = Db::table('region')->where('code', '=', $user['profile']['county'])->find();
        $this->assign('region', $region);
        // 返回结果
        $this->assign('username', $username);
        $this->assign('config', $config);
        return $this->fetch();
    }

    /**
     * 申请页面
     */
    public function join(Request $req)
    {
        // 用户账号
        $username = session('user.account.username');
        // 获取配置
        $config = Configure::get('hello.boss');
        // 状态
        $boss = Db::table('boss')->where('username', '=', $username)->find();
        if (!empty($boss) && $boss['status'] == 1) {
            $this->redirect('/boss.html');
            exit;
        }
        // 提交申请
        if ($req->isPost()) {
            try {
                // 未开启
                if (empty($config) || empty($config['enable'])) {
                    throw new \think\Exception("很抱歉、系统暂未开启该模块！");
                }
                // 用户账号
                $user = (new Account())->instance($username);
                if (empty($user['account']['status'])) {
                    throw new \think\Exception("很抱歉、您的账号已被冻结！");
                }
                if ($user['account']['authen'] != 1) {
                    throw new \think\Exception("很抱歉、请先通过实名认证！");
                }
                // 重复申请
                $boss = Db::table('boss')->where('username', '=', $username)->find();
                if (!empty($boss) && ($boss['status'] == 1 || $boss['status'] == 2)) {
                    throw new \think\Exception("很抱歉、请勿重复申请！");
                }
                // 开启事务
                Db::startTrans();
                // 获取地区
                $region = $req->param('region');
                if (empty($region)) {
                    throw new \think\Exception("很抱歉、请选择所在地区！");
                }
                $region = Db::table('region')->where('code', '=', $region)->where('type', '=', 3)->find();
                if (empty($region)) {
                    throw new \think\Exception("很抱歉、错误的地区！");
                }
                // 获取头像
                $avatar = null;
                $file = $req->file('avatar');
                if (empty($file)) {
                    throw new \think\Exception("很抱歉、请提供头像图片！");
                } else {
                    // 图片检查
                    $info = $file->validate(['ext' => 'jpg,jpeg,png'])->check();
                    if (!$info) {
                        throw new \think\Exception("很抱歉、错误的头像图片！");
                    }
                    // 存放目录
                    $folder = Env::get('root_path') . 'public/avatar/';
                    is_dir($folder . date('Ymd')) or mkdir($folder . date('Ymd'), 0777, true);
                    // 图片压缩
                    $image = \think\Image::open($file);
                    $avatar = '/' . date('Ymd') . '/' . md5(time() . mt_rand()) . '.' . $image->type();
                    $image->thumb(640, 640)->save($folder . $avatar);
                }

                // 保存到个人资料
                $data = [
                    'avatar'        =>  $avatar,
                    'province'      =>  $region['province'],
                    'city'          =>  $region['city'],
                    'county'        =>  $region['code'],
                    'update_at'     =>  $this->timestamp,
                ];
                $bool = Db::table('profile')->where('username', '=', $username)->update($data);
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、个人资料更新失败！");
                }

                // 获取头衔
                $title = $req->param('title');
                if (empty($title)) {
                    throw new \think\Exception("很抱歉、请选择头衔！");
                }
                $titleArray = explode(',', $title);
                $systemTitle = array_key_exists('title', $config) ? $config['title'] : '';
                foreach ($titleArray as $key => $value) {
                    if (stripos($systemTitle, $value) === false) {
                        throw new \think\Exception("很抱歉、【$value】不存在！");
                    }
                }

                // 保存老板资料
                if (empty($boss)) {
                    $bool = Db::table('boss')->insert([
                        'status'        =>  2,
                        'username'      =>  $username,
                        'title'         =>  $title,
                        'advantage'     =>  null,
                        'background'    =>  null,
                        'create_at'     =>  $this->timestamp,
                        'update_at'     =>  $this->timestamp,
                    ]);
                } else {
                    $bool = Db::table('boss')->where('username', '=', $username)->update([
                        'status'        =>  2,
                        'title'         =>  $title,
                        'update_at'     =>  $this->timestamp,
                    ]);
                }
                if (empty($bool)) {
                    throw new \think\Exception("很抱歉、申请失败请重试！");
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
        // 未开启
        if (empty($config)|| empty($config['enable'])) {
            $this->error('很抱歉、系统暂未开启该模块！');
            exit;
        }
        $this->assign('config', $config);

        $this->assign('boss', $boss);
        // 返回结果
        return $this->fetch();
    }
}
