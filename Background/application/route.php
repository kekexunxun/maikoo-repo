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

// use think\Route;  //导入Route类
// Route::rule('index','index/Index');  //创建路由规则
// Route::rule('banner','index/BannerList');  //创建路由规则


// use think\Route;
// Route::alias('index','index/Index');
// Route::alias('banner','index/BannerList');
// use think\Route;
// Route::alias('index','index/Index');
// Route::resource('banner','index/BannerList');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[name]'      => [
        ':id'   => ['index/Bander', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    '__alias__'	  => [
    	'index' => 'index/Index',
        'banner' => 'index/Banner',
        'shop' => 'index/Shop',
        'column' => 'index/Column',
        'minipro' => 'index/Minipro',
        'userinfo' => 'index/Userinfo',
        'search' => 'index/Search',
        'article' => 'index/Article',
        'admin' => 'index/Admin'
    ]

];
