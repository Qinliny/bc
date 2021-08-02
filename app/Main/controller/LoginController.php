<?php


namespace app\Main\controller;


use app\Model\UserDb;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use think\facade\Session;

class LoginController extends \app\BaseController
{
    /**
     * 登录页面
     * @return \think\response\View
     */
    public function login() {
        return view('login/login');
    }

    /**
     * 注册页面
     * @return \think\response\View
     */
    public function register() {
        return view('login/register');
    }

    // 校验登录的数据
    public function checkLogin() {
        $param = $this->request->post();
        // 校验数据
        try {
            $this->validate($param, [
                'account'    =>  'require',
                'password'   =>  'require',
                'verifyCode' =>  'require'
            ], [
                'account.require'   =>  '请输入账号',
                'password.require'  =>  '请输入密码',
                'verifyCode'        =>  '请输入验证码'
            ]);
        } catch (ValidateException $exception) {
            failedAjax(__LINE__, $exception->getMessage());
        }

        // 校验验证码
        if (!captcha_check($param['verifyCode'])) failedAjax(__LINE__, "验证码不正确！");
        $userInfo = UserDb::findUserByaAccount($param['account']);
        if ($userInfo === false) failedAjax(__LINE__, "查询失败！");
        if (empty($userInfo)) failedAjax(__LINE__, "账号不正确！");

        if (!password_verify($param['password'], $userInfo['pwd']))
        {
            failedAjax(__LINE__, "密码不正确！");
        }

        $info = [
            'userId'  =>  $userInfo['id'],
            'account' =>  $userInfo['account']
        ];
        session('userInfo', $info);
        return;
    }

    // 校验注册数据
    public function checkRegister() {
        $param = $this->request->post();
        // 校验数据
        $this->validateRegisterData($param);

        $installData = [
            'account'   =>  $param['account'],
            'nickname'  =>  $param['nickname'],
            'pwd'       =>  password_hash($param['password'], PASSWORD_DEFAULT),
            'type'      =>  0,
            'register_time' =>  thisTime()
        ];

        // 保存数据
        $res = UserDb::createUser($installData);
        if ($res === false) failedAjax(__LINE__,"注册失败！");
        successAjax("注册成功！");
    }

    // 检查注册数据是否合法
    private function validateRegisterData($data) {
        try {
            $this->validate($data, [
                'nickname'  =>  'require|max:10',
                'account'   =>  'require|regex:/^[0-9a-z]*$/|min:6|max:18|unique:user',
                'password'  =>  'require|regex:/^[0-9a-z]*$/|min:6|max:18',
                'confirm_password'  =>  'require|confirm:password'
            ], [
                'nickname.require'  =>  '请输入昵称',
                'nickname.max'      =>  '昵称长度不得超过10位',
                'account.require'   =>  '请输入账号',
                'account.regex'     =>  '账号只能由字母、数字组成，长度为6到18位',
                'account.min'       =>  '账号只能由字母、数字组成，长度为6到18位',
                'account.max'       =>  '账号只能由字母、数字组成，长度为6到18位',
                'account.unique'    =>  '账号已存在，请重新输入',
                'password.require'  =>  '请输入密码',
                'password.regex'    =>  '密码只能由字母、数字组成，长度为6到18位',
                'password.min'      =>  '密码只能由字母、数字组成，长度为6到18位',
                'password.max'      =>  '密码只能由字母、数字组成，长度为6到18位',
                'confirm_password.require'  =>  '请再次输入确认密码',
                'confirm_password.confirm'  =>  '两次输入的密码不一致，请重新输入'
            ]);
        } catch (ValidateException $e) {
            failedAjax(__LINE__, $e->getMessage());
        }
    }

    // 验证码
    public function verify() {
        return Captcha::create();
    }

    public function logout()
    {
        session('userInfo', null);
        return view('login/login');
    }
}