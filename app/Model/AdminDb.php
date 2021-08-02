<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;

class AdminDb
{
    // 表名
    private static $table = "admin";

    /**
     * 根据admin用户名获取管理员信息
     * @param string $username  管理员用户名
     * @return array|false|\think\Model|null
     */
    public static function findAdminByUserName(string $username) {
        try {
            return Db::table(self::$table)->where(['username'=>$username])->find();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * 根源管理员ID获取管理员信息
     * @param int $adminId  管理员ID
     * @return array|false|\think\Model|null
     */
    public static function findAdminByAdminId(int $adminId) {
        try {
            return Db::table(self::$table)->where(['id'=>$adminId])->find();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * 获取管理员列表
     * @param $page
     * @param $limit
     * @return false|\think\Paginator
     */
    public static function getAdminList($page, $limit) {
        try {
            $res = Db::table(self::$table)->paginate(['list_rows'=>$limit, 'page'=>$page]);
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }
}