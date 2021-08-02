<?php


namespace app\Api\controller;

use app\Api\tools\Send;
use app\BaseController;
use think\App;
use think\Request;

class ApiController extends BaseController
{
    use Send;

    protected $request;
    protected $params;
    protected $middleware = [];
    protected $token;


    public function __construct(App $app, Request $request)
    {
        parent::__construct($app);
        $this->request = $request;
        $this->params = $request->request();
        $this->token = $request->header('x-csrf-token');
    }

    public function generateToken()
    {
        return $this->request->buildToken('__token__', 'md5');
    }

    protected function Send($code = 20000, $msg = "", $data = array(), $header = array())
    {
        if (is_array($code)) {
            self::returnMsg($code['code'], $code['msg'], $code['data']);
        }
        self::returnMsg($code, $msg, $data, $header);
    }

    protected function getUserInfo()
    {
        if (empty($this->token)) {
            return false;
        }
        if (!session("?" . $this->token)) {
            return false;
        }
        return session($this->token);
    }

    protected function setUserInfo($userInfo)
    {
        if (!$this->token) {
            return false;
        }
        session($this->token, $userInfo);
    }


}