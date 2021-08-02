<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;

class OrderDb
{
    private static $table = "order";

    /**
     * 创建订单
     * @param $data
     * @param bool $isAll   是否批量
     * @return bool
     */
    public static function createOrder($data, $isAll = false) {
        // 下单修改：添加到用户账变明细， 扣除用户余额
        Db::startTrans();
        try {
            $moneySum = 0; $userId = null;
            if ($isAll) {
                $res = Db::table(self::$table)->insertAll($data);
                $userId = end($data)['user_id'];
                foreach ($data as $key => $value) {
                    $moneySum += $value['money'];
                }
            } else {
                $res = Db::table(self::$table)->insert($data);
                $moneySum = $data['money'];
                $userId = $data['user_id'];
            }

            $userInfo = UserDb::findUserInfoById($userId);

            // 添加到账变明细
            $balanceLogRes = Db::table('balance_log')->insert([
                'user_id'   =>  $userId,
                'money'     =>  $moneySum,
                'balance'   =>  $userInfo['coin'],
                'is_increase'   =>  0,
                'remark'    =>  '下注扣除金额',
                'create_time'   =>  thisTime()
            ]);

            // 修改余额信息
            $updateUserRes = UserDb::updateUserInfoById($userId, ['coin'=>$userInfo['coin'] - $moneySum]);
            if ($res && $balanceLogRes && $updateUserRes) {
                Db::commit();
                return true;
            } else {
                throw new Exception('下注失败', __LINE__);
            }
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 根据期数ID获取订单数据
     * @param $lotterId
     * @return bool|\think\Collection
     */
    public static function getOrderListByLotteryId($lotteryId) {
        try {
            return Db::table(self::$table)->where('lottery_id', $lotteryId)->select();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 修改订单信息
     * @param $orderId
     * @param $data
     * @return bool
     */
    public static function updateOrderInfoById($orderId, $data) {
        try {
            $res = Db::table(self::$table)->where('id', $orderId)->update($data);
            return $res > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取用户在当前游戏本期所买的总金额
     * @param $userId       用户ID
     * @param $gameId       游戏ID
     * @param $lotteryId    期数ID
     * @return false|float
     */
    public static function getUserOrderMoneySum($userId, $gameId, $lotteryId) {
        try {
            $res = Db::table(self::$table)->where([
                'user_id'   =>  $userId,
                'game_id'   =>  $gameId,
                'lottery_id'=>  $lotteryId
            ])->sum('money');
            return $res;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * 获取用户的订单信息
     * @param $userId
     * @param int $page
     * @param int $limit
     * @return false|\think\Paginator
     */
    public static function getUserOrderByUserId($userId, $page = 1, $limit = 15) {
        try {
            $res = Db::table(self::$table)->alias('a')
                ->join('game b', 'a.game_id = b.id')
                ->join('lottery c', 'a.lottery_id = c.id')
                ->field('a.*, b.game_name, c.periods, c.is_lottery, c.result')
                ->where('a.user_id', $userId)
                ->order('a.create_time', 'desc')
                ->paginate(['list_rows'=>$limit, 'page'=>$page])
                ->each(function($item) {
                    $item['content'] = json_decode($item['content'], true);
                    return $item;
                });
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getOrderList($page = 1, $limit = 15, $condition = []) {
        try {
            $res = Db::table(self::$table)->alias('a')
                ->join('game b', 'a.game_id = b.id')
                ->join('lottery c', 'a.lottery_id = c.id')
                ->join('user u', 'a.user_id = u.id')
                ->field('a.*, b.game_name, c.periods, c.is_lottery, c.result, u.account')
                ->where($condition)
                ->order('a.create_time', 'desc')
                ->paginate(['list_rows'=>$limit, 'page'=>$page])
                ->each(function($item) {
                    $item['content'] = json_decode($item['content'], true);
                    return $item;
                });
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getHistoryReport($page = 1, $limit = 15, $condition = []) {
        try {
            $result = Db::table('order')
                ->where($condition)
                ->field('count(`id`) as orderCount, sum(`money`) as money, sum(`win_amount`) as winAmount, 
                FROM_UNIXTIME(UNIX_TIMESTAMP(create_time),"%Y-%m-%d") as createTime')
                ->order('createTime desc')
                ->group("createTime")
                ->page($page, $limit)
                ->select();
            $count = Db::table('order')
                ->where($condition)
                ->field('count(`id`) as orderCount, sum(`money`) as money, sum(`win_amount`) as winAmount, 
                FROM_UNIXTIME(UNIX_TIMESTAMP(create_time),"%Y-%m-%d") as createTime')
                ->order('createTime desc')
                ->group("createTime")
                ->count();
            return [
                'items' => $result->toArray(),
                'total' => $count
            ];
        } catch (Exception $e) {
            return false;
        }
    }
}