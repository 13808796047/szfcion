<?php /*a:2:{s:63:"C:\Users\123\Code\fcc\application\index\view\account\index.html";i:1541053328;s:62:"C:\Users\123\Code\fcc\application\index\view\common\world.html";i:1541585625;}*/ ?>
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
    <title><?php echo htmlentities(app('config')->get('hello.title')); ?></title>
    
<style>
@media (max-width: 768px) {
    .col-xs-left {
        padding-left: 0.75rem !important;
        padding-right: 0.375rem !important;
    }
    .col-xs-right {
        padding-left: 0.375rem !important;
        padding-right: 0.75rem !important;
    }
    .carousel .carousel-item {
        max-height: 175px;
    }
}
.card-news .list-unstyled li:last-child {
    margin-bottom: 0 !important;
}
.stamp {
    background-size: cover;
    background-position: center center;
}
.notices {
    height: 1.5rem;
}
.notices div {
    height: 1.5rem;line-height: 1.5rem;
}
</style>

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
                    
<div class="card px-3 py-1">
    <div class="d-flex">
        <span><i class="fa fa-bullhorn text-muted"></i></span>
        <div class="notices w-100 o-hidden">
            <?php if(is_array($tradeList) || $tradeList instanceof \think\Collection || $tradeList instanceof \think\Paginator): $i = 0; $__LIST__ = $tradeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                <div class="ml-2 text-muted small clearfix"><?php echo htmlentities($item['nickname']); ?> <?php echo $item['type']==1 ? '买入' : '卖出'; ?> <?php echo htmlentities(money($item['number'])); ?><?php echo htmlentities(app('config')->get('hello.unit')); ?> <span class="float-right"><?php echo htmlentities($item['create_at']); ?></span></div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
    </div>
</div>
<?php if(!(empty($config['hello.site.home.carousel']) || (($config['hello.site.home.carousel'] instanceof \think\Collection || $config['hello.site.home.carousel'] instanceof \think\Paginator ) && $config['hello.site.home.carousel']->isEmpty()))): ?>
<div id="carousel" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
        <?php if(is_array($config['hello.site.home.carousel']) || $config['hello.site.home.carousel'] instanceof \think\Collection || $config['hello.site.home.carousel'] instanceof \think\Paginator): $i = 0; $__LIST__ = $config['hello.site.home.carousel'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;if($key == '0'): ?>
                <li data-target="#carousel" data-slide-to="<?php echo htmlentities($key); ?>" class="active"></li>
            <?php else: ?>
                <li data-target="#carousel" data-slide-to="<?php echo htmlentities($key); ?>"></li>
            <?php endif; endforeach; endif; else: echo "" ;endif; ?>
    </ol>
    <div class="carousel-inner">
        <?php if(is_array($config['hello.site.home.carousel']) || $config['hello.site.home.carousel'] instanceof \think\Collection || $config['hello.site.home.carousel'] instanceof \think\Paginator): $i = 0; $__LIST__ = $config['hello.site.home.carousel'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
            <div class="carousel-item <?php echo $key==0 ? 'active' : ''; ?>">
                <a href="<?php echo htmlentities((isset($item['link']) && ($item['link'] !== '')?$item['link']:'javascript:;')); ?>"><img class="d-block w-100" src="/upload/<?php echo htmlentities($item['image']); ?>" /></a>
            </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>
<?php endif; ?>
<div class="row row-cards mt-3">
    <div class="col-sm-12 col-md-12 col-lg-4">
        <div class="row">
            <?php if(!(empty($config['hello.payment']['enable']) || (($config['hello.payment']['enable'] instanceof \think\Collection || $config['hello.payment']['enable'] instanceof \think\Paginator ) && $config['hello.payment']['enable']->isEmpty()))): ?>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-azure mr-3">
                            <i class="fa fa-cny"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/wallet/record.html?c=8"><?php echo htmlentities(money_show($user['wallet']['rmb'])); ?></a></h4>
                            <small class="text-muted"><?php echo htmlentities(app('config')->get('hello.currencys.8.name')); ?></small>
                        </div>
                        <small class="ml-auto">
                            <a href="/wallet/counter.html?c=8" class="mr-1 text-azure d-block">充值</a>
                            <?php if(!(empty($config['hello.withdraw']['enable']) || (($config['hello.withdraw']['enable'] instanceof \think\Collection || $config['hello.withdraw']['enable'] instanceof \think\Paginator ) && $config['hello.withdraw']['enable']->isEmpty()))): ?>
                            <a href="/wallet/counter.html?c=8&t=2" class="mr-1 text-azure d-block">提现</a>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-blue mr-3" style="background-image: url(/static/image/c1.png);">
                            <!-- <i class="fa fa-bitcoin"></i> -->
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/wallet/record.html"><?php echo htmlentities(money_show($user['wallet']['money'])); ?></a></h4>
                            <small class="text-muted">可用<?php echo htmlentities(app('config')->get('hello.unit')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-gray mr-3" style="background-image: url(/static/image/c2.png);">
                            <!-- <i class="fa fa-bitcoin"></i> -->
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/wallet/record.html?c=2"><?php echo htmlentities(money_show($user['wallet']['deposit'])); ?></a></h4>
                            <small class="text-muted">冻结<?php echo htmlentities(app('config')->get('hello.unit')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!(empty(app('config')->get('hello.score.enable')) || ((app('config')->get('hello.score.enable') instanceof \think\Collection || app('config')->get('hello.score.enable') instanceof \think\Paginator ) && app('config')->get('hello.score.enable')->isEmpty()))): ?>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-azure mr-3">
                            <i class="fa fa-diamond"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/wallet/record.html?c=2"><?php echo htmlentities(money_show($user['wallet']['score'])); ?></a></h4>
                            <small class="text-muted">可用<?php echo htmlentities(app('config')->get('hello.score.unit')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-gray mr-3">
                            <i class="fa fa-diamond"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/wallet/record.html?c=2"><?php echo htmlentities(money_show($user['wallet']['score_deposit'])); ?></a></h4>
                            <small class="text-muted">冻结<?php echo htmlentities(app('config')->get('hello.score.unit')); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-12 col-xs-left">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-green mr-3">
                            <i class="fa fa-battery"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/machine.html"><?php echo $user['dashboard']['machine_count'] - $user['dashboard']['machine_expire']<0 ? 0  : htmlentities($user['dashboard']['machine_count'] - $user['dashboard']['machine_expire']); ?> <small>台</small></a></h4>
                            <small class="text-muted">运行中</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-12 col-xs-right">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-yellow mr-3">
                            <i class="fa fa-battery-empty"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/machine.html"><?php echo htmlentities($user['dashboard']['machine_expire']); ?> <small>台</small></a></h4>
                            <small class="text-muted">已过期</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-12 col-xs-left">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-green mr-3">
                            <i class="fa fa-users"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/team.html"><?php echo htmlentities($user['dashboard']['team_count']); ?></a></h4>
                            <small class="text-muted">总人数</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-12 col-xs-right">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <span class="stamp stamp-md bg-yellow mr-3">
                            <i class="fa fa-desktop"></i>
                        </span>
                        <div>
                            <h4 class="m-0"><a href="/machine.html"><?php echo htmlentities(money($user['dashboard']['power'])); ?></a></h4>
                            <small class="text-muted">总算力</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-xs-left col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title f1">常用操作</h4>
            </div>
            <div class="list-group quick-operation">
                <?php if(!(empty($config['hello.event.pool']['enable']) || (($config['hello.event.pool']['enable'] instanceof \think\Collection || $config['hello.event.pool']['enable'] instanceof \think\Paginator ) && $config['hello.event.pool']['enable']->isEmpty()))): ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/pool.html">
                    <i class="fe fe-cloud text-muted"></i>
                    <span class="ml-3">链上算力</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php endif; ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/team/inviter.html">
                    <i class="fe fe-flag text-muted"></i>
                    <span class="ml-3">链上团队</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php if(!(empty(app('config')->get('hello.contract.enable')) || ((app('config')->get('hello.contract.enable') instanceof \think\Collection || app('config')->get('hello.contract.enable') instanceof \think\Paginator ) && app('config')->get('hello.contract.enable')->isEmpty()))): ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/contract.html">
                    <i class="fe fe-star text-muted"></i>
                    <span class="ml-3">链上合约</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php endif; ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/wallet/counter.html?c=8">
                    <i class="fe fe-slack text-muted"></i>
                    <span class="ml-3">充值提现</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php if(!(empty($config['imtoken']['enable']) || (($config['imtoken']['enable'] instanceof \think\Collection || $config['imtoken']['enable'] instanceof \think\Paginator ) && $config['imtoken']['enable']->isEmpty()))): ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/imtoken.html">
                    <i class="fe fe-aperture text-muted"></i>
                    <span class="ml-3">imtoken</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php endif; ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/transfer.html">
                    <i class="fe fe-anchor text-muted"></i>
                    <span class="ml-3">点对点交易</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php if(!(empty(app('config')->get('hello.funding.enable')) || ((app('config')->get('hello.funding.enable') instanceof \think\Collection || app('config')->get('hello.funding.enable') instanceof \think\Paginator ) && app('config')->get('hello.funding.enable')->isEmpty()))): ?>
                    <a class="list-group-item d-flex justify-content-between align-items-center" href="/funding.html">
                        <i class="fe fe-star text-muted"></i>
                        <span class="ml-3">创业众筹</span>
                        <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                    </a>
                <?php endif; if(!(empty(app('config')->get('hello.calendar.enable')) || ((app('config')->get('hello.calendar.enable') instanceof \think\Collection || app('config')->get('hello.calendar.enable') instanceof \think\Paginator ) && app('config')->get('hello.calendar.enable')->isEmpty()))): ?>
                    <a class="list-group-item d-flex justify-content-between align-items-center" href="/calendar.html">
                        <i class="fe fe-star text-muted"></i>
                        <span class="ml-3">签到有礼</span>
                        <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-xs-right col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title f1">账户操作</h4>
            </div>
            <div class="list-group quick-operation">
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/account/profile.html">
                    <i class="fe fe-user text-muted"></i>
                    <span class="ml-3">个人资料</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/account/reset.html">
                    <i class="fe fe-lock text-muted"></i>
                    <span class="ml-3">修改密码</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/account/authen.html">
                    <i class="fe fe-shield text-muted"></i>
                    <span class="ml-3">实名认证</span>
                    <span class="text-muted ml-auto">
                    <?php switch($user['account']['authen']): case "1": ?><i class="fe fe-check text-green"></i><?php break; case "2": ?><i class="status-icon bg-warning"></i><?php break; case "3": ?><i class="status-icon bg-red"></i><?php break; default: ?>
                            <i class="fe fe-chevron-right"></i>
                    <?php endswitch; ?>
                    </span>
                </a>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/wallet/record.html">
                    <i class="fe fe-slack text-muted"></i>
                    <span class="ml-3">资金明细</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php if(!(empty(app('config')->get('hello.event.scratch.enable')) || ((app('config')->get('hello.event.scratch.enable') instanceof \think\Collection || app('config')->get('hello.event.scratch.enable') instanceof \think\Paginator ) && app('config')->get('hello.event.scratch.enable')->isEmpty()))): ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/scratch.html">
                    <i class="fe fe-heart text-muted"></i>
                    <span class="ml-3">幸运刮刮卡</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <?php endif; ?>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/help.html">
                    <i class="fe fe-help-circle text-muted"></i>
                    <span class="ml-3">帮助中心</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/about.html">
                    <i class="fe fe-info text-muted"></i>
                    <span class="ml-3">关于我们</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <!-- <a class="list-group-item d-flex justify-content-between align-items-center" href="/news.html">
                    <i class="fe fe-list text-muted"></i>
                    <span class="ml-3">新闻公告</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>
                <a class="list-group-item d-flex justify-content-between align-items-center" href="/contact.html">
                    <i class="fe fe-headphones text-muted"></i>
                    <span class="ml-3">联系我们</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a>

                <a class="list-group-item d-flex justify-content-between align-items-center" href="/signout.html">
                    <i class="fe fe-log-out text-muted"></i>
                    <span class="ml-3">退出登录</span>
                    <span class="text-muted ml-auto"><i class="fe fe-chevron-right"></i></span>
                </a> -->
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-4">
        <div class="card card-news">
            <div class="card-header">
                <div class="card-title f1"><a href="/news.html">平台资讯</a></div>
                <div class="card-options">
                    <div class="text-muted mr-2">数字翡翠公告、资讯</div>
                </div>
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled">
                    <?php if(is_array($news) || $news instanceof \think\Collection || $news instanceof \think\Paginator): $i = 0; $__LIST__ = $news;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$new): $mod = ($i % 2 );++$i;?>
                    <li class="media mb-3">
                        <img src="/upload/<?php echo htmlentities($new['image']); ?>" class="w-7 h-7 mr-3" />
                        <div class="media-body">
                            <h5 class="mt-0 mb-1 text-truncate" style="max-width: 13rem;"><a href="/article/<?php echo htmlentities($new['id']); ?>.html" class="font-weight-light"><?php echo htmlentities($new['title']); ?></a></h5>
                            <small class="text-muted"><?php echo htmlentities($new['date']); ?></small>
                        </div>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php if(!(empty($config['hello.popup']['switch']) || (($config['hello.popup']['switch'] instanceof \think\Collection || $config['hello.popup']['switch'] instanceof \think\Paginator ) && $config['hello.popup']['switch']->isEmpty()))): ?>
<div class="modal fade modal-popup" id="modal-popup" tabindex="-1" role="dialog" data-version="<?php echo htmlentities(sha1($config['hello.popup']['content'])); ?>" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">公告通知</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php echo $config['hello.popup']['content']; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">我知道了</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
    
<script>
require(['core', 'jquery'], function(core, $){
    $(function(){
        // 账号同步
        setTimeout(function(){
            ajax(api.account.sync, {}, function(){});
        }, 500);
        // 公告通知
        if ($('.modal-popup').length) {
            var version = $('.modal-popup').data('version');
            if (localStorage) {
                if (localStorage.popup != version) {
                    $('.modal-popup').modal();
                    localStorage.popup = version;
                }
            } else {
                $('.modal-popup').modal();
            }
        }
        // 交易列表
        if ($('.notices div').length > 1) {
            setInterval(function(){
                $('.notices div').first().slideUp(1000, function(){
                    $('.notices div').first().insertAfter($('.notices div').last());
                    $('.notices div').last().show();
                });
            }, 2000);
        }
    });
});
</script>

</body>

</html>