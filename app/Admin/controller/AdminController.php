<?php


namespace app\Admin\controller;


use app\Model\AdminDb;
use think\facade\View;

class AdminController extends BaseController
{
    /**
     * 管理员列表页面
     * @return \think\response\View
     */
    public function index() {
        $param = $this->request->param();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        // 获取管理员列表
        $list = AdminDb::getAdminList($page, $limit);
        View::assign(['list'=>$list->items(), 'count'=>$list->total(), 'page'=>$page, 'limit'=>$limit]);
        return view();
    }
}