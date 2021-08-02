<?php
namespace app\Api\route;
use think\facade\Route;

//示例
//Route::rule(":v/data-manage/synchronous-data", ":v.dataManage/synchronousData")
//    ->middleware(Permission::class, FidConst::DATA_SYNC_BTN);

Route::rule("/login","User/login");