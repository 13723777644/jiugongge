<?php
return array(
	//'配置项'=>'配置值'
	
// +----------------------------------------------------------------------
// | 数据库配置设定
// +----------------------------------------------------------------------		
	'DB_TYPE'               =>  'mysql',        // 数据库类型
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'zx_jianshen_',       // 数据库表前缀   ！开发时配置常量 ！
    'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8		

	'DB_HOST'               =>  '', // 服务器地址
    //'DB_NAME'               =>  'zxhotel',          // 数据库名
    'DB_NAME'           =>'',
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '',      //'1234QWERasdf',          // 密码
	'content'=>array(
		'dir' =>"/jiugongge/Data/"
	),
	'wxpay'  =>array(
		'applyshop_notify_url'=>"https://zx-xcx.com/minizxhotel/index.php/Api/Applyshop/wxnotifyurl",
		'wx_notify_url'       =>"https://zx-xcx.com/minizxhotel/index.php/Api/Pay/wxnotifyurl",
		'wx_rznotify_url'       =>"https://zx-xcx.com/minizxhotel/index.php/Api/Pay/wxrznotifyurl"
	),
	'weixin' => array(
		'appid' => 'wx548035f1d2d3d6df',
		'secret' =>	'46760c8b7710e355f0b9ec0134e2480d',
		'mchid'=>'',
		'key'=>''
	),
    'SHOW_PAGE_TRACE' =>TRUE,
    'qiniuyun'=>array(
        'ak'=>'n8OdQvWp6SwGCrjyk1FCraueuzniECiAxCQv7fco',
        'sk'=>'fbdMJQ_AOIVpZMJTj_wbmGwqIe_kw3G28Mqm3xA4',
        'bucket'=>'dibanzhuan',
        'url'=>'http://pdldygq4s.bkt.clouddn.com//'//一定要加刚
    ),
    'orc'=>array(
    'aid'=>'2108025346',
    'ak'=>'WSk7XcgVQXlR1soa',
        

)
);