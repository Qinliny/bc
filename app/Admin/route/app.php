<?php

namespace app\Admin\route;

use think\facade\Route;

// 后台首页
Route::get("/", "Index/index")->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);
// 登录验证码
Route::get("/verify", "Login/verify");

// 登录页面
Route::get("/login", "Login/login");
Route::get("/logout", "Login/logout");
Route::post("/login/checkLogin", "Login/checkLogin");

// 管理员管理
Route::group('/admins', function () {
    Route::get("/", "Admin/index");
})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);

// 彩种配置
Route::group('/games', function () {
    Route::get("/gamesList", "Games/gamesSettings");
    Route::post("/addGame", "Games/addGame");
    Route::get('/config', 'Games/gameConfig');
    Route::post('/saveConfig', 'Games/saveGameConfig');
    Route::post('/delGame', 'Games/delGame');
    Route::post('/findGameInfo', 'Games/findGameInfoById');
    Route::post('/editGameInfo', 'Games/editGameInfoById');
})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);

Route::group('recharge', function () {
    //充值配置
    Route::get('/chargeConfig', 'Recharge/chargeConfig');
    Route::get('/withdrawalConfig', 'Recharge/withdrawalConfig');
    Route::post('/upload', 'Recharge/upload');
    Route::post('/saveChargeConfig', 'Recharge/saveChargeConfig');
    //充值/提现记录
    Route::get('/charge', 'Recharge/charge');
    Route::get('/withdrawal', 'Recharge/withdrawal');
    Route::get('/loadChargeRecord', 'Recharge/loadChargeRecord');
    Route::get('/loadWithdrawalRecord', 'Recharge/loadWithdrawalRecord');
    Route::post('/removeById', 'Recharge/removeById');
    Route::post('/invalidStatus', 'Recharge/invalidStatus');
    Route::post('/chargeRecord', 'Recharge/chargeRecord');
})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);

//用户账户管理
Route::group('UserManage', function () {
    Route::get('/accountManage', 'UserManage/accountManage');
    Route::get('/loadData', 'UserManage/loadData');
    Route::post('/saveAllow', 'UserManage/saveAllow');
    Route::post('/removeById', 'UserManage/removeById');
    Route::post('/loadData', 'UserManage/loadData');
    //用户管理
    Route::get('/users', 'UserManage/users');
    Route::get('/getUserList', 'UserManage/getUserList');
    Route::post('/removeUserById', 'UserManage/removeUserById');
    Route::post('/changeEnable', 'UserManage/changeEnable');
    //实名认证管理
    Route::get('/userAuth', 'UserManage/userAuth');
    Route::get('/loadUserAuths', 'UserManage/loadUserAuths');
    Route::post('/authAction', 'UserManage/authAction');
    Route::post('/removeAuthById', 'UserManage/removeAuthById');
    //代理管理
    Route::get('/agency', 'UserManage/agency');
    Route::get('/loadAgencies', 'UserManage/loadAgencies');
    Route::post('/addAgencyChild', 'UserManage/addAgencyChild');
    Route::post('/enableAgency', 'UserManage/enableAgency');
    Route::post('/removeAgencyById', 'UserManage/removeAgencyById');
    Route::post('/addBalance', 'UserManage/addBalance');

})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);

Route::group('/lottery', function () {
    Route::get('/index', 'Lottery/lottery');
    Route::post('/settingLotteryResult', 'Lottery/settingLotteryResult');
})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);

// 普通下注
Route::group('/bet', function(){
    Route::get('/', 'Bet/betLog');
})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);

Route::group('/balanceLog', function () {
    Route::get('/', 'Bet/balanceLog');
})->middleware(\app\Admin\middleware\CheckLoginMiddleware::class);