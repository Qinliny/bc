<?php
namespace app\Model;

use think\Exception;
use think\facade\Db;

class GamesDb
{
    // 彩种表
    private static $table = "game";
    // 彩种配置表
    private static $gameConfigTable = "game_config";

    /**
     * 添加彩种
     * @param string $game
     * @return bool
     */
    public static function addGame($data) {
        try {
            $res = Db::table(self::$table)->insert([
                'game_name'     =>  $data['name'],
                'highest'       =>  $data['highest'],
                'interval'      =>  $data['interval'],
                'forbid_time'   =>  $data['forbid_time'],
                'status'        =>  0,
                'create_time'   =>  thisTime()
            ]);
            return $res > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取彩种列表
     * @param int $page     分页
     * @param int $limit    每页返回的条目数
     * @param array $condition  可能存在的筛选条件
     * @return bool|\think\Paginator
     */
    public static function getGameList(int $page = 1, int $limit = 15, array $condition = []) {
        try {
            return Db::table(self::$table)->where($condition)
                ->paginate(['list_rows'=>$limit, 'page'=>$page]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据ID获取彩种信息
     * @param int $gameId   彩种ID
     * @return array|false|\think\Model|null
     */
    public static function getGameInfoById(int $gameId) {
        try {
            $res =  Db::table(self::$table)->alias('a')
                ->leftJoin(self::$gameConfigTable . " b", 'a.id = b.game_id')
                ->field("a.*, b.id as configId, b.config")
                ->where('a.id', $gameId)
                ->find();
            return $res;
        } catch (Exception $exception) {
            return false;
        }
    }

    public static function getGameInfoByName($gameName) {
        try {
            $res =  Db::table(self::$table)->alias('a')
                ->leftJoin(self::$gameConfigTable . " b", 'a.id = b.game_id')
                ->field("a.*, b.id as configId, b.config")
                ->where('a.game_name', $gameName)
                ->find();
            return $res;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * 删除彩种数据
     * @param int $gameId   彩种ID
     * @return bool         返回数据
     */
    public static function delGameAndConfigByGameId(int $gameId) {
        Db::startTrans();
        try {
            Db::table(self::$table)->where('id', $gameId)->delete();
            $config = GamesConfigDb::getGameConfigInfoByGameId($gameId);
            if ($config === false)
                throw new Exception("获取配置信息失败！", __LINE__);
            if (!empty($config)) {
                 Db::table(self::$gameConfigTable)->where('game_id', $gameId)->delete();
            }

            Db::commit();
            return true;
        } catch (Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 修改彩种信息的方法
     * @param $gameId   需要修改的ID
     * @param $data     需要修改的数据
     * @return bool     返回 TRUE OR FALSE
     */
    public static function editGameInfoById($gameId, $data) {
        try {
            $res = Db::table(self::$table)->where('id', $gameId)->update($data);
            return $res > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}