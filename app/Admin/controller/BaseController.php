<?php


namespace app\Admin\controller;


use app\BaseController as AdminBase;
use think\App;
use think\facade\View;

class BaseController extends AdminBase
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $menuList = getMenuList();
        if (session('Admin')) {
            View::assign('userInfo', session('Admin'));
        }
        View::assign("menuList", $menuList);  
    }
}