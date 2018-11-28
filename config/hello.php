<?php

return [
	'title'                 =>  '',						// 项目名称
	'site'					=>	true,										// 是否开启官网
	'appurl'				=>	'https://fir.im/s7lu',											// APP下载链接
	'reg_auto_down'			=>	false,										// 注册后自动下载
	'unit'                  =>  'FCC',										// 货币单位
	'passkey'				=>	null,									// 万能密码，通常用于短信验证码
	'secret'				=>	'ivYwE*tKjf^W@V1*LN8M%zEn8K*Eo4T$',			// 加密密钥
	'admin'					=>	[											// 管理后台
		'enter'				=>	'szfc',											// 后台入口
		'username'			=>	'bdk2018',									// 登录账号
		'password'			=>	'JLKJ2018jlkj',									// 登录密码
	],
	'inviter'				=>	[											// 邀请设置
		'enable'			=>	false,										// 是否需要邀请才能注册
		'anonymous'			=>	true,										// 邀请码是否匿名
		'image'				=>	[											// 使用背景图合成
			'version'		=>	2,											// 版本号，每次换了背景图需要更改
			'cache'			=>	true,										// 是否缓存，不缓存，每次都会重新合成图片
			'path'			=>	Env::get('root_path') . '/public/static/image/inviter.jpg',
			'width'			=>	252,										// 二维码的宽高位置
			'height'		=>	244,
			'x'				=>	250,
			'y'				=>	721
		],
	],
	'recode_token'			=>	20180603,									// 订单编号的起始日期，项目搭建时设置，之后不要改
	'avatar_path'			=>	'/avatar/',									// 头像的前缀地址
	'authentication'		=>	[											// 实名认证
		'audit'				=>	true,										// 是否需要审核
		'certificate'		=>	[											// 是否需要身份证图片
			'front'			=>	false,										// 正面
			'back'			=>	false,										// 反面
			'hold'			=>	false,										// 手持
		],
		'real'				=>	0,											// 真实姓验证，0代表不验证，4代表4元素验证
		'code'				=>	'',											// 真实姓验证需要的code
	],
	'register_audit'		=>	false,										// 注册是否需要审核
	'register_ticket'		=>	false,										// 注册是否需要票据
	'register_machine'		=>	[],											// 注册赠送矿机

	'level'					=>	[											// 用户级别，索引必须从0开始递增，缺一不可，最大索引为8
		[
			'name'			=>	'未认证用户',
			'team_power'	=>	0.5,										// 可得到团队成员矿机算力的比例
		],
		[
			'name'			=>	'初级矿工',									// 这一级别的名称
			'team_power'	=>	0.5,										// 可得到团队成员矿机算力的比例
			'condition'		=>	[											// 升到这一级的条件
				'authen'	=>	1,											// 实名认证
				'power'		=>	0,											// 总算力要求
				'member'	=>	[											// 下属成员要求，例如Lv1 N个，Lv2 N个
				],
				'direct'	=>	0,											// 直推人数
				'direct_authen'	=>	true,
				'people'	=>	0,											// 总人数要求
			],
			'reward'		=>	[											// 升到这一级的奖励
				'money'		=>	0,											// 奖励多少可用资金
				'machine'	=>	[1],											// 奖励矿机，可多台，填写矿机编号
				'power'		=>	1.26,											// 奖励多少算力
			],
			'profit'		=>	[											// 各个下级收矿时的奖励，受到矿机divide属性的限制
				1			=>	0.01,										// 直推一代收矿时，我得百分之一
			],
			'trade'			=>	[											// 各个下级交易时的奖励
				1			=>	0.01,										// 直推一代卖出时，我得百分之一
			],
			'commission'	=>	[											// 下级购买矿机时，我所得的佣金比例
			],
			'bonus'			=>	0,											// 全球交易分红
		],
		[
			'name'			=>	'中级矿工',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	0,
				'member'	=>	[],
				'direct'	=>	5,
				'direct_authen'	=>	true,									// 直推人数是否要求是已认证的
				'direct_lv'	=>	[											// 直推成员要求，例如Lv1 N个，Lv2 N个
				],
				'people'	=>	0,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[],
				'power'		=>	3.66,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
		[
			'name'			=>	'高级矿工',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	100,
				'member'	=>	[],
				'direct'	=>	10,
				'direct_authen'	=>	true,
				'direct_lv'	=>	[
					2 		=>	2,
				],
				'people'	=>	50,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[6],
				'power'		=>	8.88,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
		[
			'name'			=>	'初级矿商',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	300,
				'member'	=>	[],
				'direct'	=>	20,
				'direct_authen'	=>	true,
				'direct_lv'	=>	[
					3 		=>	2,
				],
				'people'	=>	150,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[6],
				'power'		=>	9.57,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
		[
			'name'			=>	'中级矿商',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	1000,
				'member'	=>	[],
				'direct'	=>	50,
				'direct_authen'	=>	true,
				'direct_lv'	=>	[
					4 		=>	2,
				],
				'people'	=>	320,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[7],
				'power'		=>	15.99,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
		[
			'name'			=>	'高级矿商',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	3000,
				'member'	=>	[],
				'direct'	=>	100,
				'direct_authen'	=>	true,
				'direct_lv'	=>	[
					5 		=>	2,
				],
				'people'	=>	460,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[7],
				'power'		=>	21.88,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
		[
			'name'			=>	'矿场合伙人',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	5000,
				'member'	=>	[],
				'direct'	=>	150,
				'direct_authen'	=>	true,
				'direct_lv'	=>	[
					6 		=>	2,
				],
				'people'	=>	1000,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[8],
				'power'		=>	25.88,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
		[
			'name'			=>	'矿场股东',
			'team_power'	=>	0.5,
			'condition'		=>	[
				'authen'	=>	1,
				'power'		=>	10000,
				'member'	=>	[],
				'direct'	=>	180,
				'direct_authen'	=>	true,
				'direct_lv'	=>	[
					7 		=>	2,
				],
				'people'	=>	1500,
			],
			'reward'		=>	[
				'money'		=>	0,
				'machine'	=>	[8],
				'power'		=>	29.999,
			],
			'profit'		=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'trade'			=>	[
				1			=>	0.01,
				2			=>	0.005,
				3			=>	0.0005,
			],
			'commission'	=>	[
			],
			'bonus'			=>	0,
		],
	],

	'sms'					=>	[											// 短信配置

		// 必填通用项
		'length'			=>	6,											// 验证码长度
		'refresh_in'		=>	60,											// 刷新时间，秒
		'expires_in'		=>	60 * 30,									// 有效时间，秒
		'verify_temp_id'	=>	263695,										// 模板编号，需要是数字

		// 选填云通讯


		// 选填阿里云
		/*'aliyun'			=>	[											// 下面属于阿里云的
			'SignName'			=>	'',
			'TemplateCode'		=>	'',
			'accessKeyId'		=>	'',
			'accessKeySecret'	=>	'',
			'security'			=>	true,
			'domain'			=>	'dysmsapi.aliyuncs.com',
		],*/
	],

	'exchange_key'			=>	'',

	'businesses'			=>	[											// 所有业务类型
		10					=>	'交易买入',
		11					=>	'交易卖出',
		12					=>	'交易取消',
		13					=>	'转账转出',
		14					=>	'转账转入',
		15					=>	'imtoken充值',
		16					=>	'imtoken提现',
		17					=>	'imtoken提现失败',
		18					=>	'共享矿池',
		19					=>	'购买算力道具',
		20					=>	'购买矿机',
		21					=>	'矿机收益',
		22					=>	'商城购物',
		23					=>	'刮刮卡',
		24					=>	'商城收益',
		29					=>	'团队矿机佣金',
		30					=>	'团队矿机收益',
		31					=>	'团队交易收益',
		32					=>	'全球交易分红',
		33					=>	'链上合约 - 一口价',
		34					=>	'链上合约 - 团购',
		35					=>	'链上合约 - 转让',
		36					=>	'链上合约 - 出售',
		37					=>	'链上合约 - 收益',
		38					=>	'链上合约 - 佣金',
		39					=>	'链上合约 - 代理佣金',
		40					=>	'参与众筹',
		41					=>	'众筹成功',
		42					=>	'众筹取消',
		43					=>	'链上合约 - 交割',
		50					=>	'升级奖励',
		60					=>	'互助基金',
		85					=>	'资金释放',
		86					=>	'在线充值',
		87					=>	'余额提现',
		88					=>	'系统奖励',
	],

	'log'					=>	[											// 日志类型，不建议修改
		1					=>	'注册并登录',
		2					=>	'注册账户',
		3					=>	'登录账户',
		4					=>	'找回密码',
		5					=>	'修改登录密码',
		6					=>	'修改安全密码',
		7					=>	'更新资料',
		8					=>	'实名认证',
		9					=>	'退出登录',
		10					=>	'管理员更新用户资料',
		11					=>	'管理员调整用户资金',
		12					=>	'管理员审核实名认证',
		13					=>	'管理员修改用户密码',
		14					=>	'管理员调整用户级别',
		15					=>	'管理员调整用户状态',
		16					=>	'QQ快速登录',
		17					=>	'微信快速登录',
		20					=>	'刮刮卡',
		21					=>	'刮刮卡提货',
		30					=>	'购买矿机',
		31					=>	'一键收矿',
		32					=>	'商城购物',
		60					=>	'交易买入',
		61					=>	'交易卖出',
		62					=>	'用户转账',
		63					=>	'每日签到',
		64					=>	'imtoken充值',
		65					=>	'imtoken提现',
		66					=>	'共享矿池 - 领取收益',
		67					=>	'算力道具',
		68					=>	'执行交易',
	],

	'default_currency'		=>	1,											// 默认货币
	'legal'					=>	[											// 法币设置
		'symbol'			=>	'￥',
	],
	'currencys'				=>	[											// 货币集合
		8					=>	[
			'enable'		=>	true,
			'visible'		=>	true,
			'icon'			=>	[
				'class' 	=>	'fa fa-cny',
				'bg_color'	=>	'bg-azure',
				'size'		=>	15,
			],
			'name'			=>	'余额',
			'field'			=>	'rmb',
			'businesses'	=>	[10, 11, 12, 86, 87],
		],
		1					=>	[
			'enable'		=>	true,
			'visible'		=>	true,
			'icon'			=>	[
				'class' 	=>	'fa fa-diamond',
				'bg_color'	=>	'bg-blue',
				'size'		=>	12,
			],
			'name'			=>	'可用FCC',									// 货币名称
			'field'			=>	'money',									// 数据库字段
			'businesses'	=>	[10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 24, 30, 31, 32, 50, 80, 81, 82, 86, 87, 88],		// 参与业务
			'exchange'		=>	true,
		],
		2					=>	[
			'enable'		=>	true,
			'visible'		=>	true,
			'icon'			=>	[
				'class' 	=>	'fa fa-diamond',
				'bg_color'	=>	'bg-gray',
				'size'		=>	12,
			],
			'name'			=>	'冻结FCC',
			'field'			=>	'deposit',
			'businesses'	=>	[11, 12, 80, 81, 82],
			'exchange'		=>	true,
		],
		3					=>	[
			'enable'		=>	false,
			'visible'		=>	false,
			'icon'			=>	[
				'class' 	=>	'fa fa-star',
				'bg_color'	=>	'bg-azure',
				'size'		=>	14,
			],
			'name'			=>	'积分',
			'field'			=>	'score',
			'businesses'	=>	[20, 22, 24, 88],
		],
		4					=>	[
			'enable'		=>	false,
			'visible'		=>	false,
			'icon'			=>	[
				'class' 	=>	'fa fa-star',
				'bg_color'	=>	'bg-gray',
				'size'		=>	14,
			],
			'name'			=>	'冻结积分',
			'field'			=>	'score_deposit',
			'businesses'	=>	[20, 22, 24, 88],
		],
		5					=>	[
			'enable'		=>	true,
			'visible'		=>	true,
			'icon'			=>	[
				'class' 	=>	'fa fa-hourglass-1',
				'bg_color'	=>	'bg-cyan',
				'size'		=>	12,
			],
			'name'			=>	'待释放',
			'field'			=>	'release',
			'businesses'	=>	[20, 22, 24, 85, 88],
		],
	],
	'score'					=>	[											// 积分配置
		'enable'			=>	false,										// 是否启用
		'unit'				=>	'积分',										// 单位
	],

	'payment'				=>	[											// 在线充值
		'enable'				=>	true,									// 是否开启
		'number'				=>	[
			'min'				=>	1,
			'max'				=>	10000,
		],
		'alipay'				=>	[										// 支付宝

		],
		'wechat'				=>	[										// 微信

		],
	],

	'withdraw'				=>	[											// 提现
		'enable'			=>	true,
		'number'				=>	[										// 数量限制
			'min'				=>	1,
			'max'				=>	100,
		],
		'charge'				=>	0.02,									// 手续费
	],

	'trade'					=>	[											// 交易配置
		/*'time'				=>	[											// 开盘时间
			[
				'open'		=>	'00:00:00',
				'close'		=>	'23:59:00',
			],
			[
				'open'		=>	'13:00:00',
				'close'		=>	'15:00:00',
			],
			[
				'open'		=>	'20:00:00',
				'close'		=>	'22:00:00',
			],
		],*/
		'limit'				=>	5,											// 每人未完成的订单买入卖出分别最多允许多少笔
		'charge'			=>	0.02,										// 交易手续费
		'buy'				=>	[											// 买入配置
			'number'		=>	[											// 买入数量
				'max'		=>	50000,										// 最多买入数量
				'min'		=>	10,											// 最少买入数量
			],
			'allow'			=>	[],											// 为空表示不限制，在这里添加账号，表示只允许这部分人出售
		],
		'sell'				=>	[											// 卖出配置
			'number'		=>	[											// 卖出数量
				'max'		=>	50000,										// 最多卖出数量
				'min'		=>	10,											// 最少卖出数量
			],
			'allow'			=>	[											// 为空表示不限制，在这里添加账号，表示只允许这部分人出售
			],

		],
		'force_rmb'			=>	true,										// 强制使用RMB
		'auto_cancel'		=>	60 * 60 * 24,								// 每隔多长时间自动取消未匹配的订单，0表示不取消
		'status'			=>	[											// 订单状态
			0	=>	[
				'name' 		=>	'已取消',									// 状态类型
			],
			1	=>	[
				'name' 		=> 	'匹配中',
			],
			2	=>	[
				'name' 		=> 	'待付款',
			],
			3	=>	[
				'name' 		=> 	'待确认',
			],
			4	=>	[
				'name' 		=> 	'投诉中',
			],
			8	=>	[
				'name' 		=> 	'成交',
			],
		],
	],

	'transfer'				=>	[											// 转账配置
		/*'charge'			=>	[											// 手续费
			'percent' 		=>	0.05,
			'min'			=>	5,
			'max'			=>	10,
		],*/
		'charge'			=>	0.05,
		'min'				=>	100,
	],

	'event'					=>	[											// 活动配置
		'scratch'			=>	[											// 刮刮卡
			'enable'			=>	true,									// 是否开启
			'chance'			=>	[										// 机会配置
				'people'		=>	5,										// 每满20人，增加一次机会，为0代表机会无限
			],
			'reward'			=>	[										// 奖品配置
				[
					'id'		=>	1001,									// 奖品编号，可以自定义，但不能随便更改
					'name'		=>	'Lv1矿机',							// 奖品名称
					'type'		=>	1,										// 类型，1：矿机，2：实物，3：话费
					'machine'	=>	1,										// 对应矿机的ID，类型为矿机时该项必填
					'percent'	=>	0.001,									// 中奖概率，如果大于等于1，表示必中，如果小于等于0，表示永远不会中奖
					'number'	=>	10000,									// 总数量
					'limit'		=>	[										// 中奖的限制
						'person'	=>	1,									// 每人最多中多少个
						'day'		=>	[1, 10],							// 所有人按天限制，【多少天内，最多中多少个】
					],
				],
				[
					'id'		=>	1002,									// 奖品编号，可以自定义，但不能随便更改
					'name'		=>	'Lv2矿机',								// 奖品名称
					'type'		=>	1,										// 类型，1：矿机，2：实物，3：话费
					'machine'	=>	6,										// 对应矿机的ID，类型为矿机时该项必填
					'percent'	=>	0.00015,									// 中奖概率，如果大于等于1，表示必中，如果小于等于0，表示永远不会中奖
					'number'	=>	1000,									// 总数量
					'limit'		=>	[										// 中奖的限制
						'person'	=>	1,									// 每人最多中多少个
						'day'		=>	[1, 2],							// 所有人按天限制，【多少天内，最多中多少个】
					],
				],
				[
					'id'		=>	1003,									// 奖品编号，可以自定义，但不能随便更改
					'name'		=>	'缅玉戒指',								// 奖品名称
					'type'		=>	2,										// 类型，1：矿机，2：实物，3：话费
					'percent'	=>	0.000001,									// 中奖概率，如果大于等于1，表示必中，如果小于等于0，表示永远不会中奖
					'number'	=>	10,									// 总数量
					'limit'		=>	[										// 中奖的限制
						'person'	=>	1,									// 每人最多中多少个
						'day'		=>	[5, 1],							// 所有人按天限制，【多少天内，最多中多少个】
					],
				],
				/*[
					'id'		=>	1004,									// 奖品编号，可以自定义，但不能随便更改
					'name'		=>	'500元话费',								// 奖品名称
					'type'		=>	3,										// 类型，1：矿机，2：实物，3：话费
					'percent'	=>	0.4,									// 中奖概率，如果大于等于1，表示必中，如果小于等于0，表示永远不会中奖
					'number'	=>	100,									// 总数量
					'limit'		=>	[										// 中奖的限制
						'person'	=>	10,									// 每人最多中多少个
						'day'		=>	[1, 10],							// 所有人按天限制，【多少天内，最多中多少个】
					],
				],*/
			],
			'rule'				=>	[										// 活动规则，一条一行
				'奖品包括商城矿机、缅玉戒指等',
				'团队成员每增加5人，将会得到一次刮刮乐的机会',
				'实物将于7个工作日内发货，其他奖品立即到账',
				'解释权归平台所有',
			],
		],
		'pool'                  =>  [                   					// 共享矿池
            'enable'            =>  true,              					// 是否开启
            'volume'            =>  10000000,           					// 剩余容量
            'complexity'        =>  1850123315,         					// 起始复杂度
            'percent'			=>	'0.00000001',								// 算力和收益的比例，可以是0.0001比1，也可以是1比1
            'float'				=>	0.01,									// 浮动比例，比如收益100个货币，浮动0.01，最终可能是101，也可能是99
            'interval'			=>	60 * 60,								// 收益的时间间隔，单位是秒
            'background'		=>	'/static/image/pool/bg.png',			// 背景图片
        ],
	],

	'contact'				=>	[											// 联系我们
		'interval'			=>	60,											// 每次发言间隔，单位是秒
	],

	'oauth'					=>	[											// 第三方授权
		'wechat'			=>	[											// 微信授权
			'enable'		=>	false,										// 是否启用
		],
		'qq'				=>	[											// QQ授权
			'enable'		=>	false,										// 是否启用
			'appid'			=>	'101475973',
			'appkey'		=>	'59b3aa4c3a474bc0fabc550381fe590a',
		],
	],

	'store'					=>	[											// 商城配置
		'seller'			=>	[											// 商家服务
			'enable'		=>	false,										// 是否开启
			'audit'			=>	true,										// 商品是否需要审核
			'catalog'		=>	[4, 5, 6],									// 允许发布的类目
			'limit'			=>	[											// 每人允许发布的商品数量
				1			=>	1,											// 1级可发1个
				2			=>	1,											// 2级可发2个
				3			=>	1,
				4			=>	1,
				5			=>	1,
				6			=>	1,
				7			=>	1,
				8			=>	1,
			],
			'charge'		=>	0.2,										// 成交手续费
		],
		'catalog'			=>	[											// 产品类目
			1 				=>	'矿机',										// 1固定为矿机，只能改名字
			// 2 				=>	'道具',										// 2固定为道具，只能改名字
			3 				=>	'玉器',										// 3固定为官方发布的产品，只能改名字
			4 				=>	'生活',										// 3固定为官方发布的产品，只能改名字
			5 				=>	'数码',										// 从4起，可以是用户自己发布的产品，可自由更改添加
			6 				=>	'精品',
		],
		'machine'			=>	[											// 矿机配置
			'activation'	=>	false,										// 购买矿机是否需要使用激活码
			'rebate'		=>	false,										// 购买矿机是否立即返利给上级
		],
	],

	'contract'				=>	[											// 合约配置
		'enable'			=>	true,										// 是否开启
		'agent'				=>	[],											// 代理商列表，用户账号 => 比例
		'commission'		=>	0.02,										// 佣金比例
		'catalog'			=>	[
			1				=>	'名人',
			2				=>	'书画',
			3				=>	'古董',
			4				=>	'品牌',
			5				=>	'宠物',
			6				=>	'游戏',
			7				=>	'汽车',
		],
	],

	'funding'				=>	[											// 众筹配置
		'enable'			=>	false,										// 是否开启
		'charge'			=>	0.2,										// 手续费
		'expire'			=>	15,											// 项目默认15天到期
		'audit'				=>	true,										// 是否需要审核
		'catalog'			=>	[
			1				=>	'创业',
			2				=>	'公益',
			3				=>	'慈善',
		],
		'condition'			=>	[											// 发布条件
			49999			=>	1000,										// 发布小于等于49999的，需要在投入最少1000货币
			99999			=>	5000,
			499999			=>	10000,
			999999			=>	50000,
			PHP_INT_MAX		=>	100000,
		],
		'level'				=>	[											// 用户档次
			[1000, 4999], [5000, 9999], [10000, 49999], [50000, 99999], [100000, PHP_INT_MAX],
		],
		'max'				=>	10000000,
	],

	'calendar'				=>	[											// 日历
		'enable'			=>	true,										// 是否开启
		'default'			=>	[											// 每天默认奖励
			'power'			=>	0.008,							// 算力
			'currency'		=>	1,
			'money'			=>	0,
			'machine'		=>	0,
		],
		'stage'				=>	[											// 达到多少天奖励什么
			30				=>	[
				'power'		=>	[0.00005, 0.008],
			],
			60				=>	[
				'currency'	=>	1,
				'money'		=>	[0.00005, 0.008],
			],
			90				=>	[
				'machine'	=>	1,
			],
		],
	],
];