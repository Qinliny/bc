<?php


namespace app\Agent\controller;


use app\Model\UserDb;
use think\facade\Session;

class MembersController extends BaseController
{
    public function index()
    {
        return view('/members/index');
    }

    public function load()
    {
        $params = request()->param();
        $userInfo = Session::get('Agent');
        $params['agentId'] = $userInfo['id'];
        $count = 0;
        $data = UserDb::loadMembersByAgentId($params,$count);
        $result = [
            'data' => $data,
            'code' => 0,
            'count' => $count
        ];
        return json($result);
    }
}