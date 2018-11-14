<?php /*a:2:{s:63:"C:\Users\123\Code\fcc\application\index\view\account\reset.html";i:1541984813;s:62:"C:\Users\123\Code\fcc\application\index\view\common\world.html";i:1541585625;}*/ ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="keywords" content="比特币挖矿机,蚂蚁矿机,阿瓦隆矿机,暴风比特,FCC,数字翡翠,翡翠">
    <meta name="description" content="FCC是全球领先的数字资产交易平台，成立于2018年，目前提供超过百种数字资产的交易及投资，交易平台包含FCC币交易、币币交易、创新数字货币以及杠杆交易板块。">
    <meta name="renderer" content="webkit" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="format-detection" content="telephone=no, email=no" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover, shrink-to-fit=no" />
    <meta http-equiv="Content-Language" content="en" />
    <meta name="msapplication-TileColor" content="#2d89ef" />
    <meta name="theme-color" content="#4188c9" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="MobileOptimized" content="320" />
    <link rel="icon" href="/favicon.ico?2" type="image/x-icon" />
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico?2" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/assets/css/dashboard.css?3" />
    <link rel="stylesheet" href="/static/css/global.css?3" />
    <title>修改密码</title>
    
    <style>
        @media (max-width: 320px) {
        #headerMenuCollapse .nav-item {
            padding: 0 .6rem;
        }
    }
    #daodream-container .daodream-launcher-button {
        opacity: 0;pointer-events: none;
    }
    </style>
    <script>
    (function(i, s, o, g, r, a, m) {
        i["DaoVoiceObject"] = r;
        i[r] = i[r] || function() {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o), m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        a.charset = "utf-8";
        m.parentNode.insertBefore(a, m)
    })(window, document, "script", ('https:' == document.location.protocol ? 'https:' : 'http:') + "//widget.daovoice.io/widget/f98b4156.js", "daovoice")
    </script>
</head>

<body>
    <!-- content -->
    <div class="page">
        <div class="page-main">
            <div class="header py-4">
                <div class="container">
                    <div class="d-flex">
                        <a class="header-brand" href="/account.html"><img src="/static/image/logo.png" class="header-brand-img" alt="tabler logo"></a>
                        <div class="d-flex order-lg-2 ml-auto">
                            <div class="nav-item d-xs-flex">
                                <?php if(app('session')->get('platform') != 'app'): if(!(empty(app('config')->get('hello.appurl')) || ((app('config')->get('hello.appurl') instanceof \think\Collection || app('config')->get('hello.appurl') instanceof \think\Paginator ) && app('config')->get('hello.appurl')->isEmpty()))): if($platform == 'android'): ?>
                                <a href="<?php echo htmlentities(app('config')->get('hello.appurl')); ?>" target="_blank" class="btn btn-sm btn-outline-success btn-app-download">APP 下载</a>
                                <?php endif; else: ?>
                                <a href="javascript:;" class="btn btn-sm btn-outline-success btn-app-download" data-toggle="tooltip" data-original-title="敬请期待">APP 下载</a>
                                <?php endif; endif; ?>
                            </div>
                            <div class="d-xs-flex">
                                <a class="nav-link icon" href="/news.html"><i class="fe fe-bell"></i></a>
                            </div>
                            <div class="dropdown">
                                <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                                <span class="avatar me-avatar" style="background-image: url(<?php echo avatar(app('session')->get('user.profile.avatar'), app('session')->get('user.profile.idcard')); ?>);"><span class="avatar-status bg-green"></span></span>
                                <span class="ml-2 d-none d-lg-block">
                                    <span class="text-default"><?php echo htmlentities(app('session')->get('user.profile.nickname')); ?></span>
                                    <small class="text-muted d-block mt-1"><?php echo htmlentities(app('config')->get('hello.level')[app('session')->get('user.account.type')]['name']); ?></small>
                                </span>
                            </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                    <a class="dropdown-item" href="/account/profile.html">
                                    <i class="dropdown-icon fe fe-user"></i> 个人资料
                                </a>
                                    <a class="dropdown-item" href="/account/reset.html">
                                    <i class="dropdown-icon fe fe-lock"></i> 修改密码
                                </a>
                                    <a class="dropdown-item" href="/account/authen.html">
                                    <i class="dropdown-icon fe fe-shield"></i> 实名认证
                                </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="/help.html">
                                    <i class="dropdown-icon fe fe-help-circle"></i> 帮助文档
                                </a>
                                    <a class="dropdown-item" href="/signout.html">
                                    <i class="dropdown-icon fe fe-log-out"></i> 退出登录
                                </a>
                                </div>
                            </div>
                        </div>
                        <!-- <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                        <span class="header-toggler-icon"></span>
                    </a> -->
                    </div>
                </div>
            </div>
            <div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
                <div class="container">
                    <div class="row align-items-center">
                        <!-- <div class="col-lg-3 ml-auto header-search-div">
                        <form class="input-icon my-3 my-lg-0">
                            <input type="search" class="form-control header-search" placeholder="Search&hellip;" tabindex="1">
                            <div class="input-icon-addon">
                                <i class="fe fe-search"></i>
                            </div>
                        </form>
                    </div> -->
                        <div class="col-lg order-lg-first">
                            <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                                <li class="nav-item">
                                    <a href="/account.html" class="nav-link<?php echo app('request')->path()=='account' || app('request')->path() == ''?' active' : ''; ?>">
                                    <span><i class="fe fe-home"></i></span>
                                    <span>首页</span>
                                </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/team.html" class="nav-link<?php echo app('request')->path()=='team'?' active' : ''; ?>">
                                    <span><i class="fe fe-users"></i></span>
                                    <span>团队</span>
                                </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/machine.html" class="nav-link<?php echo app('request')->path()=='machine'?' active' : ''; ?>">
                                    <span><i class="fe fe-cpu"></i></span>
                                    <span>矿机</span>
                                </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/market.html" class="nav-link<?php echo app('request')->path()=='market'?' active' : ''; ?>">
                                    <span><i class="fe fe-globe"></i></span>
                                    <span>市场</span>
                                </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/store.html" class="nav-link<?php echo app('request')->path()=='store'?' active' : ''; ?>">
                                    <span><i class="fe fe-shopping-cart"></i></span>
                                    <span>商城</span>
                                </a>
                                </li>
                                <li class="nav-item">
                                    <a href="javascript:daovoice('openMessages');" class="nav-link<?php echo app('request')->path()=='kefu'?' active' : ''; ?>">
                                    <span><i class="fe fe-headphones"></i></span>
                                    <span>客服</span>
                                </a>
                                </li>
                                <li hidden class="nav-item d-md-block d-lg-block">
                                    <a href="/help.html" class="nav-link<?php echo app('request')->path()=='help'?' active' : ''; ?>">
                                    <span><i class="fe fe-help-circle"></i></span>
                                    <span>帮助</span>
                                </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="my-3 my-md-5">
                <div class="container container-padding">
                    
<div class="card mx-auto reset" style="max-width: 22rem;">
    <div class="card-body">
        <div class="dimmer">
            <div class="loader"></div>
            <div class="dimmer-content">
                <div class="card-title mb-4">
                    <div class="text-black mb-1">修改密码</div>
                    <div class="text-muted" style="font-size: .9rem;">您可以选择修改登录或安全密码</div>
                </div>
                <div class="form-group">
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item mb-0">
                            <input type="radio" name="type" value="1" class="selectgroup-input" />
                            <span class="selectgroup-button">登录密码</span>
                        </label>
                        <label class="selectgroup-item mb-0">
                            <input type="radio" name="type" value="2" class="selectgroup-input" />
                            <span class="selectgroup-button">安全密码</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">登录密码</label>
                    <input type="password" maxlength="32" name="password" class="form-control" placeholder="请输入新的登录密码" />
                </div>
                <div class="form-group">
                    <label class="form-label">确认密码</label>
                    <input type="password" maxlength="32" name="confirm" class="form-control" placeholder="再输入一次登录密码" />
                </div>
                <div class="form-group">
                    <label class="form-label">验证码</label>
                    <div class="input-group">
                        <input type="text" class="form-control" maxlength="4" name="captcha" placeholder="验证码" />
                        <span class="input-group-append" id="basic-addon2">
                            <span class="input-group-text captcha-touch">
                                <img src="<?php echo url('service/captcha'); ?>" class="captcha" />
                            </span>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">短信验证码</label>
                    <div class="input-group">
                        <input type="text" class="form-control" maxlength="6" name="verify_code" placeholder="手机验证码" />
                        <span class="input-group-append">
                            <button class="btn btn-secondary btn-send" type="button">发送短信</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        <button type="submit" class="btn btn-primary">修改密码</button>
    </div>
</div>

                </div>
            </div>
        </div>
        <footer class="footer d-xs-none d-sm-none d-lg-block">
            <div class="container">
                <div class="row align-items-center flex-row-reverse">
                    <div class="col-auto ml-lg-auto">
                        <div class="row align-items-center"><?php echo htmlentities(date('Y-m-d g:i a',time())); ?></div>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
                        Copyright © 2018 <a href="."><?php echo htmlentities(app('config')->get('hello.title')); ?></a>. All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <div hidden>
        <script src="https://s13.cnzz.com/z_stat.php?id=1273772622&web_id=1273772622" language="JavaScript"></script>
    </div>
    <script type="text/javascript" src="/assets/js/require.min.js"></script>
    <script type="text/javascript" src="/static/js/global.js?4"></script>
    <script>
    daovoice('init', {
        app_id: "f98b4156",
        user_id: "<?php echo htmlentities(app('session')->get('user.account.uid')); ?>",
        name: "<?php echo htmlentities(app('session')->get('user.profile.nickname')); ?>",
    }, {
        launcher: {
            disableLauncherIcon: false,
            defaultEnterView: 'list',
        }
    });
    daovoice('update');
    </script>
    
<script type="text/javascript" src="/static/js/reset.js"></script>

</body>

</html>