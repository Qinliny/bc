<?php


namespace app\Api\controller;

use app\Api\logic\UserLogic;
use app\Api\tools\Send;
use think\Request;

class UserController
{
    use Send;

    public function login(Request $req)
    {
        $params = $req->param();
        $token = $req->buildToken('__token__', 'md5');
        $userLogic = new UserLogic();

        $result = $userLogic->login($params);
        if ($result['code'] == 20000) {
            session($token, $result['data']);
            cookie('lottery', $token);
            $result['data']['token'] = $token;
        }
        Send::returnMsg($result['code'], $result['msg'], $result['data']);
    }

}