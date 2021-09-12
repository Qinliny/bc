<?php


namespace app\Main\controller;


use app\Model\GamesDb;
use app\Model\LotteryDb;
use app\Model\OrderDb;
use app\Model\UserDb;
use think\App;
use think\facade\Cache;
use think\facade\Session;
use think\facade\View;
use Workerman\Lib\Timer;

class BaseController extends \app\BaseController
{
    protected $userId;
    protected $account;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $userInfo = Session::get('userInfo');
        $this->userId = $userInfo['userId'];
        $this->account = $userInfo['account'];
        $userData = UserDb::findUserInfoById($this->userId);
        // 获取彩种
        $gamesList = GamesDb::getGameList(1, 100);
        $data = $gamesList->items();
        $last_names = array_column($data,'sort');
        array_multisort($last_names,SORT_DESC, $data);
        $gameTop9 = array_slice($data, 0, 9);
        View::assign(["gameList" => $gameTop9, 'userInfo' => $userInfo, 'accountInfo'=>$userData]);
    }
}