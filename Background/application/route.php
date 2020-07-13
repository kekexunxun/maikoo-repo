<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    '__alias__'	  => [
    	'index' => 'index/Index',
        'article' => 'index/Article',
        'login' => 'index/Index/login',
        'checkLogin' => 'index/Index/checkLogin',
        'logout' => 'index/Index/logout',
        'mini' => 'index/Mini',
        'fangte' => 'index/Fangte',
        'wxpay' => 'Wxpay',
        'sms'   =>  'index/Sms',
        'wxpay' => 'index/Wxpay',
        'printlist'=>'index/print',
        'couponlist'=>'index/couponlist',
        'courierfill'=>'index/courierfill',
        'courierdofill'=>'index/courierdofill',
        'mulimg_download'=>'index/mulimg_download',
        'couponadd'=>'index/couponadd',
        'coupondoadd'=>'index/coupondoadd',
        'complain'=>'index/complain',
        'tracklist'=>'index/tracklist',
        'trackadd'=>'index/trackadd',
        'trackdoadd'=>'index/trackdoadd',
        'trackdelete'=>'index/trackdelete',
        'trackupdate'=>'index/trackupdate',
        'sizelist'=>'index/sizelist',
        'sizelistupdate'=>'index/sizelistupdate'
    ]

];
