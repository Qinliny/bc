<?php


namespace app\Admin\middleware;


use think\facade\Session;

class CheckLoginMiddleware
{
    public function handle($request, \Closure $next) {
        if (Session::has('Admin')) {
            return $next($request);
        } else {
            // 未登录
            if ($request->isPost()) {
                failedAjax(__LINE__, "请登录后再进行操作");
            } else {
                return redirect('/admin/login');
            }
        }
    }
}