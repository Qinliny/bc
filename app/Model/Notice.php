<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;

class Notice
{
    private static $table = "notice";

    public static function saveNotice($type, $content) {
        try {
            return Db::table(self::$table)->insert([
                'type'  =>  $type,
                'content'   =>  $content,
                'create_time'   =>  thisTime()
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取管理员列表
     * @param $page
     * @param $limit
     * @return false|\think\Paginator
     */
    public static function getNoticeList($page, $limit) {
        try {
            $res = Db::table(self::$table)->paginate(['list_rows'=>$limit, 'page'=>$page]);
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }
}