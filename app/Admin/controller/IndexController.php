<?php
namespace app\Admin\controller;

use app\Model\Notice;
use think\facade\View;

class IndexController extends BaseController
{
    public function index() {
        return view('index/index');
    }

    public function notice() {
        $param = request()->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $list = Notice::getNoticeList($page, $limit);
        View::assign(['list'=>$list->items(), 'count'=>$list->total(), 'page'=>$page, 'limit'=>$limit]);
        return view('index/notice');
    }

    public function createNotice() {
        return view('index/createNotice');
    }

    public function saveNotice() {
        $param = request()->post();
        $res = Notice::saveNotice($param['title'], $param['content']);
        if ($res) {
            successAjax("添加成功");
        } else {
            failedAjax(__LINE__, "添加失败");
        }
    }
}