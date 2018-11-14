<?php /*a:2:{s:64:"C:\Users\123\Code\fcc\application\index\view\account\forgot.html";i:1541985064;s:62:"C:\Users\123\Code\fcc\application\index\view\common\hello.html";i:1541053328;}*/ ?>
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
    <title>Forgot Password</title>
    
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
                    
<div class="card forgot" method="post">
	<div class="card-body p-6">
		<div class="dimmer">
			<div class="loader"></div>
			<div hid1den class="basic">
				<div class="card-title">
					<div class="lead">找回您的密码</div>
					<div class="text-muted">在此之前，您需要进行身份验证！</div>
				</div>
				<div class="form-group">
					<input type="tel" maxlength="11" name="username" class="form-control" placeholder="输入您的手机号码" />
				</div>
				<div class="input-group">
	            	<input type="text" class="form-control" maxlength="4" name="captcha" placeholder="验证码" />
	            	<span class="input-group-append" id="basic-addon2">
	                	<span class="input-group-text captcha-touch">
	                		<img src="<?php echo url('service/captcha'); ?>" class="captcha" />
	                	</span>
	            	</span>
	            </div>
				<div class="form-group">
					<div class="input-group">
						<input type="text" class="form-control" maxlength="6" name="verify_code" placeholder="手机验证码" />
						<span class="input-group-append">
							<button class="btn btn-secondary btn-send" type="button">发送短信</button>
						</span>
					</div>
				</div>
	            <div class="form-footer">
					<button type="submit" class="btn btn-primary btn-block">继续，下一步</button>
				</div>
			</div>
			<div hidden class="reset">
				<div class="card-title">
					<div class="lead">接下来</div>
					<div class="text-muted">请重新设置您的登录密码！</div>
				</div>
				<div class="form-group">
					<label class="form-label">登录密码</label>
					<input type="password" maxlength="32" name="password" class="form-control" placeholder="请输入新的登录密码" />
				</div>
				<div class="form-group">
					<label class="form-label">确认密码</label>
					<input type="password" maxlength="32" name="confirm" class="form-control" placeholder="再输入一次登录密码" />
				</div>
				<div class="form-footer">
					<button type="submit" class="btn btn-primary btn-block">重置密码</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="text-center text-muted">
<?php if(empty(app('request')->get('i')) || ((app('request')->get('i') instanceof \think\Collection || app('request')->get('i') instanceof \think\Paginator ) && app('request')->get('i')->isEmpty())): ?>
	算了，<a href="./signin.html">我再想想！</a>
<?php else: ?>
	算了，<a href="./signin.html?i=<?php echo htmlentities(app('request')->get('i')); ?>">我再想想！</a>
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

<script src="/static/js/forgot.js"></script>

</body>
</html>