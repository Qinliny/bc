<?php


namespace app\Admin\controller;


use app\Model\AdminDb;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use think\facade\Session;

class LoginController extends BaseController
{

    // 登录页面
    public function login() {
        return view('login/index');
    }

    // 校验后台登录数据
    public function checkLogin() {
        $param = $this->request->post();
        // 校验数据
        try {
            $this->validate($param, [
                'username'  =>  'require',
                'password'  =>  'require',
                'verifyCode'    =>  'require'
            ], [
                'username.require'  =>  '请输入用户名！',
                'password.require'  =>  '请输入密码！',
                'verifyCode'        =>  '请输入验证码！'
            ]);
        } catch (ValidateException $exception) {
            failedAjax(__LINE__, $exception->getMessage());
        }

        // 校验验证码
        if (!captcha_check($param['verifyCode'])) failedAjax(__LINE__, "验证码不正确！");

        $adminInfo = AdminDb::findAdminByUserName($param['username']);
        if ($adminInfo === false) failedAjax(__LINE__, "查询失败！");
        if (empty($adminInfo)) failedAjax(__LINE__, "用户名不正确！");

        if (!password_verify($param['password'], $adminInfo['password']))
        {
            failedAjax(__LINE__, "密码不正确！");
        }
        // TODO 校验通过，或许需要写入日志
        $info = [
            'adminId'   =>  $adminInfo['id'],
            'adminUserName' =>  $adminInfo['username']
        ];

        Session::set('Admin', $info);
        return;
    }

    // 验证码
    public function verify() {
        return Captcha::create();
    }

    public function logout()
    {
        session('Admin', null);
        return view('login/index');
    }
}