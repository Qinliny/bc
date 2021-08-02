<?php


namespace app\Admin\controller;


use app\Model\BalanceLogDb;
use app\Model\GamesDb;
use app\Model\OrderDb;

class BetController extends BaseController
{
    // 普通下注
    public function betLog() {
        $param = $this->request->get();
        $page = isset($param['page']) && $param['page'] > 0 ? $param['page'] : 1;
        $limit = isset($param['limit']) && $param['limit'] > 0 ? $param['limit'] : 15;
        $condition = [];
        $orderNo = $account = $game = null;
        if (isset($param['orderNo']) && !empty($param['orderNo'])) {
            $condition[] = [
                'a.order_no', '=', $param['orderNo']
            ];
            $orderNo = $param['orderNo'];
        }

        if (isset($param['account']) && !empty($param['account'])) {
            $condition[] = [
                'u.account', '=', $param['account']
            ];
            $account = $param['account'];
        }

        if (isset($param['game']) && !empty($param['game'])) {
            $condition[] = [
                'a.game_id', '=', $param['game']
            ];
            $game = $param['game'];
        }
        $result = OrderDb::getOrderList($page, $limit, $condition);
        // 获取所有的彩种
        $gameList = GamesDb::getGameList(1, 1000);
        if ($result === false) {
            abort(500, "获取彩种列表失败");
        }

        return view('bet/betLog', ['list'=>$result->items(), 'count'=>$result->total(),
        'page'=>$page, 'limit'=>$limit, 'orderNo'=>$orderNo,'account'=>$account,'game'=>$game,
            'gameList'=>$gameList->items()]);
    }

    // 账变明细
    public function balanceLog() {
        $param = $this->request->get();
        $page = isset($param['page']) && $param['page'] > 0 ? $param['page'] : 1;
        $limit = isset($param['limit']) && $param['limit'] > 0 ? $param['limit'] : 15;
        $condition = [];
        $account = null;
        if (isset($param['account']) && !empty($param['account'])) {
            $condition[] = [
                'u.account', '=', $param['account']
            ];
            $account = $param['account'];
        }
        $result = BalanceLogDb::getBalanceLogList($page, $limit, $condition);
        if ($result === false) {
            abort(500, "获取账变明细失败！");
        }
        return view('bet/balanceLog', ['list'=>$result->items(), 'count'=>$result->total(), 'page'=>$page,
            'limit'=>$limit, 'account'=>$account]);
    }
}