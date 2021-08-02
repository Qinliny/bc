<?php

namespace app\Agent\route;

use app\Agent\middleware\CheckLoginMiddleware;
use think\facade\Route;

// 代理首页
Route::get("/", "Index/index")->middleware(CheckLoginMiddleware::class);
Route::get("/statistical", "Index/index")->middleware(CheckLoginMiddleware::class);
// 登录验证码
Route::get("/verify", "Login/verify");


// 登录页面
Route::get("/login", "Login/login");
Route::get("/logout", "Login/logout");
Route::post("/login/checkLogin", "Login/checkLogin");

//下级会员管理
Route::group('/Members',function (){
    Route::get("/", "Members/index");
    Route::get("Index", "Members/index");
    Route::get("load", "Members/load");
});


