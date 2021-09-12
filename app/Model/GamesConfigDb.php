<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;

class GamesConfigDb
{
    // 彩种配置表
    private static $table = "game_config";

    /**
     * 根据彩种ID获取彩种配置信息
     * @param int $gameId   彩种ID
     * @return array|false|\think\Model|null
     */
    public static function getGameConfigInfoByGameId(int $gameId, $gameType) {
        try {
            return Db::table(self::$table)->where('game_id', $gameId)
                ->where('type', $gameType)
                ->find();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * 添加彩种配置
     * @param $config   json序列化后的配置
     * @param $gameId   彩种id
     * @return bool     TRUE OR FALSE
     */
    public static function createGameConfig($config, $gameId, $gameType) {
        try {
            $result = Db::table(self::$table)->insert([
                'config'    =>  $config,
                'game_id'   =>  $gameId,
                'status'    =>  0,
                'type'      =>  $gameType,
                'create_time'   =>  thisTime(),
                'update_time'   =>  thisTime()
            ]);
            return $result > 0;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * 修改彩种配置数据
     * @param $config       具体的配置
     * @param $configId     当前配置的ID
     * @return bool         TRUE OR FALSE
     */
    public static function updateGameConfig($config, $configId) {
        try {
            $result = Db::table(self::$table)->where('id', $configId)->update([
                'config'    =>  $config,
                'update_time'   =>  thisTime()
            ]);
            return $result > 0;
        } catch (Exception $exception) {
            return false;
        }
    }
}