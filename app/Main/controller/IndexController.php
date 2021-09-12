<?php

namespace app\Main\controller;


use app\Model\BalanceLogDb;
use app\Model\GamesDb;
use app\Model\LotteryDb;
use app\Model\OrderDb;
use app\Model\UserDb;
use think\facade\Db;

class IndexController extends BaseController
{
    // 首页
    public function index() {
        return view("index/index");
    }

    // 我的下注页面
    public function myOrder() {
        $param = request()->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $list = OrderDb::getUserOrderByUserId($this->userId, $page, $limit);
        return view("index/myOrder", ['list'=>$list->items(), 'count'=>$list->total(),
            'page'=>$page, 'limit'=>$limit, 'type'=>"最新注单"]);
    }

    // 个人中心
    public function personalCenter() {
        // 获取个人信息
        return view('index/personalCenter', ['type'=>'个人中心']);
    }

    // 修改个人信息
    public function setPersonalCenter () {
        $param = request()->post();
        $res = UserDb::updateUserInfoById($this->userId, [
            'name'  =>  $param['name'],
            'phone' =>  $param['phone'],
            'qq'    =>  $param['qq'],
            'email' =>  $param['email']
        ]);
        successAjax("用户信息修改成功");
    }

    // 修改密码
    public function setPasssword() {
        $param = request()->post();
        $res = UserDb::updateUserInfoById($this->userId, [
            'pwd'  =>  password_hash($param['new_password'],PASSWORD_DEFAULT)
        ]);
        successAjax("用户密码修改成功");
    }

    // 个人账变明细
    public function getBalanceLog() {
        $param = request()->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $list = BalanceLogDb::getBalanceLogList($page, $limit, ['user_id'=>$this->userId]);
        if ($list === false) {
            abort(500, "服务器出现异常");
        }
        return view('index/balanceLog', ['list'=>$list->items(), 'count'=>$list->total(),
            'page'=>$page, 'limit'=>$limit, 'type'=>"账变明细"]);
    }

    // 开奖结果
    public function getResult() {
        $param = request()->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $condition = [
            'is_lottery' => 2
        ];
        $gameId = null;
        if (isset($param['game_id']) && !empty($param['game_id'])) {
            $gameId = $param['game_id'];
            $condition['game_id'] = $gameId;
        }
        $gameList = GamesDb::getGameList(1, 100);
        $list = LotteryDb::getLotteryList($page, $limit, $condition);
        if ($list === false) {
            abort(500, "服务器出现异常");
        }
        return view('index/result', ['list'=>$list->items(), 'count'=>$list->total(),
            'page'=>$page, 'limit'=>$limit, 'type'=>"开奖结果", 'gameList'=>$gameList->items(), 'gameId'=>$gameId]);
    }

    // 今日已结
    public function todayClose() {
        $param = request()->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $todayStartTime = strtotime(date("Y-m-d",time()));
        $todayEndTime = $todayStartTime + 86400 - 1;
        $condition = [
            ['a.create_time', 'between time', [thisTime($todayStartTime), thisTime($todayEndTime)]],
            ['a.is_win', '=', 1],
            ['a.user_id', '=', $this->userId]
        ];
        $list = OrderDb::getOrderList($page, $limit, $condition);
        return view('index/todayClose', ['list'=>$list->items(), 'count'=>$list->total(),
            'page'=>$page, 'limit'=>$limit, 'type'=>"今日已结"]);
    }

    // 历史报表
    public function historyReport() {
        $param = request()->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $condition = [
            ['user_id', '=', $this->userId]
        ];
        $list = OrderDb::getHistoryReport($page,  $limit, $condition);
        if ($list === false) {
            abort(500, "服务器出现异常");
        }
        foreach ($list['items'] as $keyy => &$value) {
            if ($value['winAmount'] - $value['money'] < 0) {
                $value['isWin'] = 0;
            } else {
                $value['isWin'] = 1;
            }
        }

        return view('index/historyReport', ['list'=>$list['items'], 'count'=>$list['total'],
            'page'=>$page, 'limit'=>$limit, 'type'=>"历史报表"]);
    }

    public function gameRule() {
        $type = request()->get('type');
        $gameName = request()->get('game_name');
        $gamesList = GamesDb::getGameList(1, 100);
        $gameName = !empty($gameName) ? $gameName : $gamesList->items()[0]['game_name'];
        return view('index/gameRule', ['type'=>'游戏规则', 'list'=>$gamesList->items(), 'gameName'=>$gameName,
            'gameType'=>$type]);
    }
}