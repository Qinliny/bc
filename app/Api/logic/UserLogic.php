<?php


namespace app\Api\logic;


use think\facade\Db;

class UserLogic extends BaseLogic
{
    public function login($params)
    {
        if (empty($params['account']) || empty($params['pwd'])) {
            return $this->bad('请填写账号密码');
        }
        $withoutField = ['coin_pwd', 'register_ip', 'register_time', 'update_tiem'];

        $user = Db::table('user')->where('account', $params['account'])->withoutField($withoutField)->find();
        if (!$user || $user['is_del'] == 1) {
            return $this->bad('找不到用户');
        }
        if (!password_verify($params['pwd'], $user['pwd'])) {
            return $this->bad('密码错误');
        }
        if ($user['enable'] == 0) {
            return $this->bad('您的账户已被禁用');
        }

        unset($user['pwd']);
        return $this->ok('登录成功', $user);
    }
}