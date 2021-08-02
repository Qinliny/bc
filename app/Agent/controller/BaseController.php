<?php


namespace app\Agent\controller;


use app\BaseController as AdminBase;
use think\App;
use think\facade\View;

class BaseController extends AdminBase
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        $menuList = getAgentMenuList();
        if (session('Agent')) {
            View::assign('userInfo', session('Agent'));
        }
        View::assign("menuList", $menuList);
    }
}