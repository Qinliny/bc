<?php


namespace app\Agent\controller;


use app\Model\AdminDb;
use app\Model\UserDb;
use http\Client\Curl\User;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use think\facade\Session;

class LoginController extends BaseController
{

    // 登录页面
    public function login()
    {
        return view('login/index');
    }

    // 校验后台登录数据
    public function checkLogin()
    {
        $param = $this->request->post();
        // 校验数据
        try {
            $this->validate($param, [
                'username' => 'require',
                'password' => 'require',
                'verifyCode' => 'require'
            ], [
                'username.require' => '请输入用户名！',
                'password.require' => '请输入密码！',
                'verifyCode' => '请输入验证码！'
            ]);
        } catch (ValidateException $exception) {
            failedAjax(__LINE__, $exception->getMessage());
        }

        // 校验验证码
        if (!captcha_check($param['verifyCode'])) failedAjax(__LINE__, "验证码不正确！");
        $userInfo = UserDb::findUserByaAccount($param['username']);

        if ($userInfo['type'] == 1 || $userInfo['type'] == 2) {
            failedAjax(__LINE__, '您不是代理，无法登录该平台。');
        }
        if (!password_verify($param['password'], $userInfo['pwd'])) {
            failedAjax(__LINE__, "密码不正确！");
        }

        if ($userInfo['enable'] != 1) {
            failedAjax(__LINE__, "账户已被禁用！");
        }

        $info = [
            'id' => $userInfo['id'],
            'nickname' => $userInfo['nickname']
        ];

        Session::set('Agent', $info);
        return;
    }

    // 验证码
    public function verify()
    {
        return Captcha::create();
    }

    public function logout()
    {
        session('Agent', null);
        return view('login/index');
    }
}