<?php


namespace app\Model;


use think\Exception;
use think\facade\Db;

class UserDb
{
    private static $table = "user";

    /**
     * 添加会员
     * @param array $data 添加的数据
     * @return bool
     */
    public static function createUser($data = [])
    {
        try {
            $res = Db::table(self::$table)->insert($data);
            return $res > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据账号获取用户信息
     * @param string $account 用户账号
     * @return array|bool|\think\Model|null
     */
    public static function findUserByaAccount(string $account)
    {

        $res = Db::table(self::$table)->where(['account' => $account, 'is_del' => 0])->find();
        if (!$res) {
            throw new \Exception('找不到用户', 1);
        }
        return $res;
    }

    /**
     * 据用户id更改是否启用的状态
     * @param string $id
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function changeEnableById(string $id)
    {
        $rs = Db::table(self::$table)->where('id', $id)->find();
        if (!$rs | $rs['is_del'] == 1) {
            throw new \Exception('找不到数据', 1);
        }
        $rs['enable'] = $rs['enable'] == 1 ? 0 : 1;
        Db::table(self::$table)->save($rs);
    }

    /**
     * 根据id软删除用户
     * @param string $id
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function sortDeleteUserById(string $id)
    {
        $rs = Db::table(self::$table)->where('id', $id)->find();
        if (!$rs | $rs['is_del'] == 1) {
            throw new \Exception('找不到数据,或数据已变更', 1);
        }
        $rs['is_del'] = 1;
        Db::table(self::$table)->save($rs);
    }

    /**
     * 添加总代理
     * @param $params
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function addRootAgency($params)
    {
        $where = [];
        $where[] = ['account', '=', $params['account']];
        $where[] = ['is_del', '=', 0];
        $is_exist = Db::table(self::$table)
            ->where($where)
            ->find();
        if ($is_exist) {
            throw new \Exception('账号已存在', 1);
        }
        $params['pwd'] = password_hash($params['pwd'], PASSWORD_DEFAULT);
        $params['coin_pwd'] = password_hash($params['coin_pwd'], PASSWORD_DEFAULT);
        $params['type'] = 2;
        $params['coin'] = 0;
        $params['fcoin'] = 0;
        $params['register_time'] = date('Y-m-d H:i:s');
        $params['register_ip'] = request()->ip();;

        $rs = Db::table(self::$table)->insert($params);
        if (!$rs) {
            throw new \Exception('添加失败', 1);
        }
    }


    /**
     * 根据代理id查询会员
     * @param $params username,page,limit,agentId
     * @param bool $count
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function loadMembersByAgentId($params, &$count = false)
    {
        $where = [];
        if (!empty($params['username'])) {
            $where[] = ['nickname', 'like', '%' . $params['username'] . '%'];
        }
        $where[] = ['type', '=', 0];
        $where[] = ['is_del', '=', 0];
        $where[] = ['parent_id', '=', $params['agentId']];
        $data = Db::table(self::$table)
            ->where($where)
            ->page($params['page'], $params['limit'])
            ->select();
        if ($count != false) {
            $count = Db::table(self::$table)
                ->where($where)
                ->count();
        }

        return $data;
    }

    /**
     * 根据用户ID修改用户信息
     * @param $userId   用户ID
     * @param $data 需要修改的信息
     * @throws \think\db\exception\DbException
     */
    public static function updateUserInfoById($userId, $data = []) {
        $res = Db::table(self::$table)->where('id', $userId)->update($data);
        if (!$res) {
            throw new \Exception('用户信息修改失败！', 1);
        }
        return true;
    }

    /**
     * 根据用户ID获取用户信息
     * @param $userId
     * @return array|\think\Model
     */
    public static function findUserInfoById($userId) {
        $res = Db::table(self::$table)->where('id', $userId)->find();
        if (!$res) {
            throw new \Exception('用户信息不存在', 1);
        }
        return $res;
    }

}