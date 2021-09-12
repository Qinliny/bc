<?php

use think\facade\Route;


Route::get("/", "Index/index")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/myOrder", "Index/myOrder")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/personalCenter", "Index/personalCenter")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);
Route::post("/setPersonalCenter", "Index/setPersonalCenter")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::post("/setPasssword", "Index/setPasssword")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/balanceLog", "Index/getBalanceLog")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/result", "Index/getResult")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/todayClose", "Index/todayClose")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/gameRule", "Index/gameRule")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/history", "Index/historyReport")
    ->middleware(app\Main\middleware\CheckLoginMiddleware::class);

Route::get("/login", "Login/login");
Route::get("/register", "Login/register");
Route::post("/register/checkRegister", "Login/checkRegister");
Route::post("/login/checkLogin", "Login/checkLogin");
Route::get("/logout", "Login/logout");

// 登录验证码
Route::get("/verify", "Login/verify");

Route::group('/game', function () {
    Route::get("/", "Game/index");
    Route::get("/sixLottery", "Game/sixLottery");
    Route::get('/carLottery', 'Game/carLottery');
    Route::get('/everyColor', 'Game/everyColor');
    Route::post("/saveOrder", "Game/saveOrder");
})->middleware(app\Main\middleware\CheckLoginMiddleware::class);
