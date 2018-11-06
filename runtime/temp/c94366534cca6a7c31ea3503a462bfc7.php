<?php /*a:2:{s:64:"C:\Users\123\Code\fcc\application\index\view\account\signin.html";i:1541053327;s:62:"C:\Users\123\Code\fcc\application\index\view\common\hello.html";i:1541053328;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="renderer" content="webkit" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="format-detection" content="telephone=no, email=no"/>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, shrink-to-fit=no" />
    <meta http-equiv="Content-Language" content="en" />
    <meta name="msapplication-TileColor" content="#2d89ef" />
    <meta name="theme-color" content="#4188c9" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="HandheldFriendly" content="True" />
    <meta name="MobileOptimized" content="320" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/assets/css/dashboard.css" />
    <link rel="stylesheet" href="/static/css/global.css" />
    <title>Sign In</title>
    
</head>

<body>
<!-- content -->
<div class="page">
    <div class="page-single">
        <div class="container">
            <div class="row">
                <div class="col col-login mx-auto">
                    <div class="text-center mb-6">
                        <img src="/static/image/logo.png" class="h-7" alt="">
                        
                    </div>
                    
<div class="card signin" method="post">
    <div class="card-body p-6">
		<div class="card-title">登录到您的帐户</div>
		<div class="form-group">
			<label class="form-label">手机号码</label>
			<input tabindex="1" type="tel" maxlength="11" name="username" class="form-control" placeholder="手机号码" />
		</div>
		<div class="form-group">
			<label class="form-label">登录密码
				<?php if(empty(app('request')->param())): ?>
				<a href="/forgot.html" class="float-right small">忘记密码</a>
				<?php else: ?>
				<a href="/forgot.html?<?php echo query(app('request')->query()); ?>" class="float-right small">忘记密码</a>
				<?php endif; ?>
			</label>
			<input tabindex="2" type="password" maxlength="32" name="password" class="form-control" placeholder="请在此输入" />
		</div>
		<div class="form-footer">
			<button tabindex="3" type="submit" class="btn btn-primary btn-block">登录账号</button>
			<?php if(!(empty(app('config')->get('hello.oauth.wechat.enable')) || ((app('config')->get('hello.oauth.wechat.enable') instanceof \think\Collection || app('config')->get('hello.oauth.wechat.enable') instanceof \think\Paginator ) && app('config')->get('hello.oauth.wechat.enable')->isEmpty()))): ?>
				<a hidden href="/oauth/wechat/login.html" class="btn btn-success btn-block btn-wechat"><i class="fa fa-wechat mr-1"></i> 微信登录</a>
			<?php endif; if(!(empty(app('config')->get('hello.oauth.qq.enable')) || ((app('config')->get('hello.oauth.qq.enable') instanceof \think\Collection || app('config')->get('hello.oauth.qq.enable') instanceof \think\Paginator ) && app('config')->get('hello.oauth.qq.enable')->isEmpty()))): ?>
				<a hidden href="/oauth/qq/login.html" class="btn btn-success btn-block btn-qq"><i class="fa fa-qq mr-1"></i> QQ登录</a>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="text-center text-muted">
<?php if(empty(app('request')->param())): ?>
	还没有帐户? <a href="/signup.html">立即注册</a>
<?php else: ?>
	还没有帐户? <a href="/signup.html?<?php echo query(app('request')->query()); ?>">立即注册</a>
<?php endif; ?>
</div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- script -->
<div hidden><script src="https://s13.cnzz.com/z_stat.php?id=1273772622&web_id=1273772622" language="JavaScript"></script></div>
<script type="text/javascript" src="/assets/js/require.min.js"></script>
<script type="text/javascript" src="/static/js/global.js?1"></script>

<script src="/static/js/signin.js?1"></script>

</body>
</html>