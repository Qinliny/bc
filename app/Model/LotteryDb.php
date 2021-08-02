<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;
use think\facade\Log;

class LotteryDb
{
    private static $table = "lottery";

    private static $gameTable = "game";

    /**
     * 根据彩种ID获取最新的游戏期数以及开奖的信息
     * @param $gameId
     * @return array|bool|\think\Model|null
     */
    public static function getLotteryInfoByGameId($gameId) {
        try {
            return Db::table(self::$table)->where('game_id', $gameId)
                        ->order('periods', 'desc')
                        ->find();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据id获取信息
     * @param $id
     * @return array|bool|\think\Model|null
     */
    public static function findLotteryInfoById($id) {
        try {
            return Db::table(self::$table)->where('id', $id)->find();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 添加数据
     * @param $data
     * @param bool $isAll
     * @return bool
     */
    public static function createLotteryInfo($data, $isAll = false, $getLastInsID = false) {
        try {
            if ($isAll) {
                $res = Db::table(self::$table)->insertAll($data);
            } else {
                $res = Db::table(self::$table)->insert($data, $getLastInsID);
                if ($getLastInsID) {
                    return $res;
                }
            }
            return $res > 0;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * 根据ID修改数据
     * @param $id
     * @param $updateData
     * @return bool
     */
    public static function updateLotteryInfoById($id, $updateData) {
        try {
            $res = Db::table(self::$table)->where('id', $id)->update($updateData);
            return $res > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取开奖数据列表
     * @param int $page     分页
     * @param int $limit    条目数
     * @param array $condition  获取条件
     * @return bool|\think\Paginator
     */
    public static function getLotteryList($page = 1, $limit = 15, $condition = []) {
        try {
            $res = Db::table(self::$table)->alias('a')
                ->join(self::$gameTable. " b", 'a.game_id = b.id')
                ->field('a.*, b.game_name')
                ->where($condition)
                ->order('lottery_time', 'desc')
                ->paginate(['list_rows'=>$limit, 'page'=>$page])
                ->each(function($item) {
                    if (!empty($item['result'])) {
                        $res = json_decode($item['result'], true);
                        if (!empty($res)) {
                            $item['result'] = implode('、', $res);
                        } else {
                            $item['result'] = null;
                        }
                    } else {
                        $item['result'] = null;
                    }
                    return $item;
                });
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getLastLottery($gameId) {
        try {
            return Db::table(self::$table)->alias('a')
                ->join(self::$gameTable. " b", 'a.game_id = b.id')
                ->field('a.*, b.game_name')
                ->where(['a.game_id'=>$gameId,'a.is_lottery'=>2])
                ->order('periods', 'desc')
                ->find();
        } catch (Exception $e) {
            return false;
        }
    }
}