<?php /*a:2:{s:64:"C:\Users\123\Code\fcc\application\index\view\account\signup.html";i:1541757895;s:62:"C:\Users\123\Code\fcc\application\index\view\common\hello.html";i:1541053328;}*/ ?>
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
    <title>Sign Up</title>
    
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
                        
<?php if(!(empty(app('request')->get('avatar')) || ((app('request')->get('avatar') instanceof \think\Collection || app('request')->get('avatar') instanceof \think\Paginator ) && app('request')->get('avatar')->isEmpty()))): ?>
<img class="avatar" src="<?php echo htmlentities(app('request')->get('avatar')); ?>" />
<?php endif; ?>

                    </div>
                    
<div class="card signup" method="post">
	<div class="card-body p-6">
		<div class="dimmer">
			<div class="loader"></div>
			<div class="dimmer-content basic">
				<?php if(!(empty(app('config')->get('hello.oauth.qq.enable')) || ((app('config')->get('hello.oauth.qq.enable') instanceof \think\Collection || app('config')->get('hello.oauth.qq.enable') instanceof \think\Paginator ) && app('config')->get('hello.oauth.qq.enable')->isEmpty()))): ?>
				<a hidden href="/oauth/qq/login.html" class="quick quick-qq"><i class="fa fa-qq mr-1"></i></a>
				<?php endif; if(!(empty(app('config')->get('hello.oauth.wechat.enable')) || ((app('config')->get('hello.oauth.wechat.enable') instanceof \think\Collection || app('config')->get('hello.oauth.wechat.enable') instanceof \think\Paginator ) && app('config')->get('hello.oauth.wechat.enable')->isEmpty()))): ?>
				<a hidden href="/oauth/wechat/login.html" class="quick quick-wechat"><i class="fa fa-wechat mr-1"></i></a>
				<?php endif; ?>
				<div class="card-title">
					<?php if(!(empty(app('request')->get('nickname')) || ((app('request')->get('nickname') instanceof \think\Collection || app('request')->get('nickname') instanceof \think\Paginator ) && app('request')->get('nickname')->isEmpty()))): ?>
					<div class="lead"><?php echo htmlentities(app('request')->get('nickname')); ?>，欢迎你！</div>
					<?php else: ?>
					<div class="lead">注册新账户</div>
					<?php endif; ?>
					<div class="text-muted">首先，您需要进行身份验证！</div>
				</div>
				<div class="form-group">
					<label hidden class="form-label d-sm-block">手机号码</label>
					<input type="tel" maxlength="11" name="username" class="form-control" placeholder="手机号码" />
				</div>
				<div class="input-group">
					<input type="text" class="form-control" maxlength="4" name="captcha" placeholder="验证码" />
					<span class="input-group-append" id="basic-addon2" style="cursor: pointer" >
						<span class="input-group-text captcha-touch p-1">
							<img src="<?php echo url('service/captcha'); ?>" class="captcha w-100 h-100" />
						</span>
					</span>
				</div>
				<div class="form-group">
					<div class="input-group">
						<input type="text" class="form-control" maxlength="6" name="verify_code" placeholder="手机验证码" />
						<span class="input-group-append">
							<button class="btn btn-secondary btn-send" type="button">获取短信</button>
						</span>
					</div>
				</div>
				<div class="form-group">
					<label hidden class="form-label d-sm-block">邀请人</label>
					<?php if(empty(app('request')->get('i')) || ((app('request')->get('i') instanceof \think\Collection || app('request')->get('i') instanceof \think\Paginator ) && app('request')->get('i')->isEmpty())): ?>
					<input type="text" maxlength="11" name="inviter" class="form-control" placeholder="邀请码<?php echo app('config')->get('hello.inviter.enable')?'':'、选填'; ?>" />
					<?php else: ?>
					<input type="text" maxlength="11" name="inviter" readonly="true" class="form-control" value="<?php echo htmlentities(app('request')->get('i')); ?>" />
					<?php endif; ?>
				</div>
				<div class="form-footer">
					<button type="submit" class="btn btn-primary btn-block">立即注册</button>
					<!-- <a href=""><img src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon48_wx_button.png" alt=""></a> -->
				</div>
			</div>
			<div hidden class="dimmer-content pwd">
				<div class="card-title">
					<div class="lead">接下来</div>
					<div class="text-muted">请为您的账户设置密码</div>
				</div>
				<div class="form-group">
					<label hidden class="form-label d-sm-block">登录密码</label>
					<input type="password" maxlength="32" name="password" class="form-control" placeholder="登录密码" />
				</div>
				<div class="form-group">
					<label hidden class="form-label d-sm-block">确认登录密码</label>
					<input type="password" maxlength="32" name="confirm" class="form-control" placeholder="再输入一次登录密码" />
				</div>
				<div class="form-group">
					<label hidden class="form-label d-sm-block">安全密码</label>
					<input type="password" maxlength="32" name="safeword" class="form-control" placeholder="安全密码" />
				</div>
				<div class="form-group">
					<label hidden class="form-label d-sm-block">确认安全密码</label>
					<input type="password" maxlength="32" name="safewordConfirm" class="form-control" placeholder="再输入一次安全密码" />
				</div>
				<div class="form-footer">
					<div class="d-flex">
						<button type="submit" class="btn btn-primary w-45">完成</button>
						<button type="button" class="btn btn-secondary btn-skip w-45 ml-auto">跳过</button>
					</div>
				</div>
			</div>
			<div hidden class="dimmer-content alert success mb-0">
				<h4>恭喜您</h4>
				<p class="mb-2">账户注册成功啦！</p>
				<p class="mb-2">请记住您的密码：</p>
				<p class="text-blue mb-2"><b>123456</b></p>
				<p class="mb-0"><span class="s">-</span>秒 后将自动 <a href="/account.html">进入首页</a></p>
			</div>
		</div>
		<?php if(!(empty(app('config')->get('hello.oauth.wechat.enable')) || ((app('config')->get('hello.oauth.wechat.enable') instanceof \think\Collection || app('config')->get('hello.oauth.wechat.enable') instanceof \think\Paginator ) && app('config')->get('hello.oauth.wechat.enable')->isEmpty()))): ?>
		<div hidden class="oauth oauth-wechat">
			<button type="button" class="close oauth-close"></button>
			<div class="alert alert-icon">
				<i class="fe fe-bell mr-2" aria-hidden="true"></i> 系统检测到您正在使用微信访问，是否通过微信进行注册？
				<p class="text-blue mt-3"><a class="oauth-close">谢谢，不必了</a></p>
				<p class="text-muted mt-3"><span class="oauth-time"></span> 秒后自动关闭</p>
			</div>
			<a href="/oauth/wechat/login.html" class="btn btn-success btn-login"><i class="fa fa-wechat mr-1"></i> 通过微信注册</a>
		</div>
		<?php endif; if(!(empty(app('config')->get('hello.oauth.qq.enable')) || ((app('config')->get('hello.oauth.qq.enable') instanceof \think\Collection || app('config')->get('hello.oauth.qq.enable') instanceof \think\Paginator ) && app('config')->get('hello.oauth.qq.enable')->isEmpty()))): ?>
		<div hidden class="oauth oauth-qq">
			<button type="button" class="close oauth-close"></button>
			<div class="alert alert-icon">
				<i class="fe fe-bell mr-2" aria-hidden="true"></i> 系统检测到您正在使用QQ访问，是否通过QQ进行注册？
				<p class="text-blue mt-3"><a class="oauth-close">谢谢，不必了</a></p>
				<p class="text-muted mt-3"><span class="oauth-time"></span> 秒后自动关闭</p>
			</div>
			<a href="/oauth/qq/login.html" class="btn btn-success btn-login"><i class="fa fa-qq mr-1"></i> 通过QQ注册</a>
		</div>
		<?php endif; ?>
	</div>
</div>
<div class="text-center text-muted">
	<?php if(empty(app('request')->param())): ?>
	已有账号了? <a href="/signin.html">立即登录</a>
	<?php else: ?>
	已有账号了? <a href="/signin.html?<?php echo query(app('request')->query()); ?>">立即登录</a>
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

<script type="text/javascript">
	var avatar = '<?php echo htmlentities(app('request')->get('avatar')); ?>',
		nickname = '<?php echo htmlentities(app('request')->get('nickname')); ?>',
		o = '<?php echo htmlentities(app('request')->get('o')); ?>',
		reg_auto_down = '<?php echo htmlentities(app('config')->get('hello.reg_auto_down')); ?>',
		appurl = '<?php echo htmlentities(app('config')->get('hello.appurl')); ?>';
	var invite = {
		enable: parseInt('<?php echo htmlentities((app('config')->get('hello.inviter.enable') ?: 0)); ?>', 10),
		anonymous: parseInt('<?php echo htmlentities((app('config')->get('hello.inviter.anonymous') ?: 0)); ?>', 10),
	}
</script>
<script src="/static/js/signup.js?3"></script>

</body>
</html>