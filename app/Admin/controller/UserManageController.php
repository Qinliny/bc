<?php


namespace app\Admin\controller;


use app\Model\BalanceLogDb;
use app\Model\UserDb;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Db;

class UserManageController extends BaseController
{
    public function accountManage()
    {
        return view('/UserManage/accountManage');
    }

    public function loadData()
    {
        $params = request()->param();
        $whereOr = [];
        $whereAnd = [];
        if (!empty($params['username'])) {
            $whereOr[] = ['u.name', 'like', '%' . $params['username'] . '%'];
            $whereOr[] = ['u.nickname', 'like', '%' . $params['username'] . '%'];
        }
        if (!empty($params['type'])) {
            $whereAnd[] = ['wt.type', '=', $params['type']];
        }
        if (!empty($params['allow'])) {
            $whereAnd[] = ['wt.is_allow', '=', $params['allow'] == "allow" ? 1 : 0];
        }

        $whereAnd[] = ['wt.is_del', '=', 0];

        $result = [];
        $list = Db::table('withdrawal_type')
            ->alias('wt')
            ->leftJoin('user u', 'u.id = wt.user_id')
            ->whereOr($whereOr)
            ->where($whereAnd)
            ->field(['wt.id', 'u.nickname username', 'wt.type', 'wt.account',
                'wt.qr_code', 'wt.is_allow', 'add_time', 'process_time'])
            ->page($params['page'], $params['limit'])
            ->select();
        $count = Db::table('withdrawal_type')
            ->alias('wt')
            ->where($whereAnd)
            ->whereOr($whereOr)
            ->count();
        $result['count'] = $count;
        $result['data'] = $list;
        $result['code'] = 0;
        return json($result);
    }

    public function saveAllow()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax(1, "参数错误");
        }
        $where = ['id' => $params['id'], 'is_del' => 0];
        $data = Db::table('withdrawal_type')->where($where)->find();
        if (!$data) {
            failedAjax('1', '没有找到数据');
        }
        if ($data['is_allow'] == 0) {
            $map = [
                'type' => $params['type'],
                'user_id' => $data['user_id'],
                'is_allow' => 0,
                'is_del' => 0
            ];
            $isAllow = Db::table("withdrawal_type")->where($map)->find();
            if ($isAllow && $isAllow['id'] != $data['id']) {
                failedAjax('1', '该类型账户已有认证。');
            }
        }
        $data['is_allow'] = $data['is_allow'] == 0 ? 1 : 0;
        $data['process_time'] = date('Y-m-d H:i:s');
        Db::table('withdrawal_type')->save($data);
        successAjax('修改状态成功');
    }

    public function removeById()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax(1, "参数错误");
        }
        $where = ['id' => $params['id'], 'is_del' => 0];
        $data = Db::table('withdrawal_type')->where($where)->find();
        if (!$data) {
            failedAjax('1', '没有找到数据');
        }
        $data['is_del'] = 1;
        $data['process_time'] = date('Y-m-d H:i:s');
        Db::table('withdrawal_type')->save($data);
        successAjax('删除成功');
    }

    public function users()
    {
        return view('/UserManage/users');
    }

    public function getUserList()
    {
        $params = request()->param();
        $where = [];
        if (!empty($params['username'])) {
            $where[] = ['u.nickname', 'like', '%' . $params['username'] . '%'];
        }

        if (!empty($params['enable'])) {
            if ($params['enable'] == 'none') $params['enable'] = 0;
            $where[] = ['u.enable', '=', $params['enable']];
        }
        $where[] = ['u.is_del', '=', 0];
        $where[] = ['u.type', '=', 0];

        $users = Db::table('user')
            ->alias('u')
            ->where($where)
            ->page($params['page'], $params['limit'])
            ->select();

        $array = [];
        foreach ($users->toArray() as $key => &$val) {
            $val['coin'] = sprintf("%1\$.2f", $val['coin']);
            $array[] = $val;
        }

        $count = Db::table('user')
            ->alias('u')
            ->where($where)
            ->count();
        $result = [
            'code' => 0,
            'data' => $array,
            'count' => $count
        ];
        return json($result);
    }

    public function removeUserById()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax(1, "参数错误");
        }
        UserDb::sortDeleteUserById($params['id']);
        successAjax('删除成功');
    }

    public function changeEnable()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax(1, "参数错误");
        }
        UserDb::changeEnableById($params['id']);
        successAjax('修改成功');
    }

    public function userAuth()
    {
        return view('/UserManage/userAuth');
    }

    public function loadUserAuths()
    {
        $params = request()->param();
        $where = [];
        if (!empty($params['username'])) {
            $where[] = ['name', 'like', '%' . $params['username'] . '%'];
        }
        $where[] = ['is_del', '=', 0];

        $data = Db::table('user_auth')
            ->where($where)
            ->page($params['page'], $params['limit'])
            ->select();
        $count = Db::table('user_auth')
            ->where($where)
            ->count();
        $result = [
            'code' => 0,
            'data' => $data,
            'count' => $count
        ];
        return json($result);
    }

    public function authAction()
    {
        $params = request()->param();
        if (empty($params['id']) || empty($params['status'])) {
            failedAjax('1', '参数错误');
        }
        $authRecord = Db::table('user_auth')->where('id', $params['id'])->find();
        if (!$authRecord || $authRecord['is_del'] == 1) {
            failedAjax('1', '找不到数据');
        }
        if ($authRecord['status'] != 0) {
            failedAjax('1', '记录已变更');
        }
        $user = Db::table('user')->where('id', $authRecord['user_id'])->find();
        if (!$user || $user['is_del'] == 1) {
            failedAjax('1', '找不到用户');
        }
        if ($user['enable'] != 1) {
            failedAjax('1', '账户已禁用，无法实名');
        }
        if ($params['status'] == 1) {
            $is_exist = Db::table('user_auth')
                ->where(['user_id' => $authRecord['user_id'], 'status' => 1, 'is_del' => 0])
                ->count();
            if ($is_exist != 0) {
                failedAjax('1', '该账户已通过实名，无法重复实名');
            }
        }
        $authRecord['status'] = $params['status'];
        $authRecord['process_time'] = date('Y-m-d H:i:s');
        Db::table('user_auth')->save($authRecord);
        successAjax('操作成功');
    }

    public function removeAuthById()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax('1', '参数错误');
        }
        $record = Db::table('user_auth')->where('id', $params['id'])->find();
        if (!$record || $record['is_del'] == 1) {
            failedAjax('1', '数据已不存在');
        }
        $record['is_del'] = 1;
        Db::table('user_auth')->save($record);
        successAjax('操作成功');

    }

    public function agency()
    {
        return view('/UserManage/agency');
    }

    public function loadAgencies()
    {
        $params = request()->param();
        $where = [];
        if (!empty($params['username'])) {
            $where[] = ['nickname', 'like', '%' . $params['username'] . '%'];
        }
        $where[] = ['is_del', '=', 0];
        $where[] = ['type', '=', 2];

        $data = Db::table('user')
            ->where($where)
            ->page($params['page'], $params['limit'])
            ->select();
        $temp = [];
        foreach ($data as $item) {
            $item['agency_count'] = $this->buildAgencyData($item);
            $temp[] = $item;
        }
        $count = Db::table('user')
            ->where($where)
            ->count();

        $result = [
            'code' => 0,
            'data' => $temp,
            'count' => $count
        ];
        return json($result);
    }

    private function buildAgencyData($data)
    {
        if (!$data) {
            return 0;
        }
        $users = Db::table('user')
            ->where(['is_del' => 0, 'parent_id' => $data['id'], 'enable' => 1])
            ->select();
        $count = count($users);
        foreach ($users as $user) {
            $count += $this->buildAgencyData($user);
        }
        return $count;
    }

    public function addAgencyChild()
    {
        $params = request()->param();
        UserDb::addRootAgency($params);
        successAjax('添加成功');
    }

    public function enableAgency()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax('1', '参数错误');
        }
        UserDb::changeEnableById($params['id']);
        successAjax('操作成功');
    }

    public function removeAgencyById()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax('1', '参数错误');
        }
        UserDb::sortDeleteUserById($params['id']);
        successAjax('删除成功');
    }

    public function addBalance() {
        $param = request()->post();
        try {
            $this->validate($param, [
                'userId' => 'require',
                'money' =>  'require|integer'
            ], [
                'userId.require'=>  '参数异常！',
                'money.require' =>  '请输入要添加的金额',
                'money.integer' =>  '金额必须为正整数'
            ]);
        } catch (ValidateException $e) {
            failedAjax('1', $e->getMessage());
        }
        $userId = $param['userId'];
        // 判断用户是否存在
        $userInfo = UserDb::findUserInfoById($param['userId']);
        // 修改余额信息
        UserDb::updateUserInfoById($userId, ['coin'=>$userInfo['coin'] + $param['money']]);
        // 添加到资金变动日志
        BalanceLogDb::addBalanceLog([
            'user_id'   =>  $userInfo['id'],
            'money'     =>  $param['money'],
            'balance'   =>  $userInfo['coin'],
            'is_increase'   =>  1,
            'remark'    =>  '管理员添加余额',
            'create_time'   =>  thisTime()
        ]);
        successAjax('余额添加成功');
    }

}