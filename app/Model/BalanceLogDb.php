<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;

class BalanceLogDb
{
    private static $table = 'balance_log';

    public static function getBalanceLogList($page = 1, $limit = 15, $condition = []) {
        try {
            return Db::table(self::$table)->alias('a')
                ->join('user u', 'a.user_id = u.id')
                ->where($condition)
                ->field('a.*, u.account')
                ->order('a.create_time', 'desc')
                ->paginate(['list_rows'=>$limit, 'page'=>$page])
                ->each(function($item) {
                    $item['money'] = sprintf("%1\$.2f", $item['money']);
                    $item['balance'] = sprintf("%1\$.2f", $item['balance']);
                    return $item;
                });
        } catch (Exception $e) {
            return false;
        }
    }

    public static function addBalanceLog($data) {
        try {
            $result = Db::table(self::$table)->insert($data);
            return $result > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}