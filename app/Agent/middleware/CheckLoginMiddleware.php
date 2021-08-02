<?php


namespace app\Agent\middleware;


use think\facade\Session;

class CheckLoginMiddleware
{
    public function handle($request, \Closure $next) {
        if (Session::has('Agent')) {
            return $next($request);
        } else {
            // 未登录
            if ($request->isPost()) {
                failedAjax(__LINE__, "请登录后再进行操作");
            } else {
                return redirect('/agent/login');
            }
        }
    }
}