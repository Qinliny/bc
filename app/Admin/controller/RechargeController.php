<?php


namespace app\Admin\controller;

use think\facade\Db;
use think\facade\Filesystem;
use think\facade\View;
use think\Route;

class RechargeController extends BaseController
{
    public function chargeConfig()
    {
        $config = Db::table('config')->where('id', 1)->find();
        View::assign('config', $config);
        return view("/recharge/chargeConfig");
    }

    public function upload()
    {
        $files = request()->file();
        try {
            validate(['image' => 'filesize:10240|fileExt:jpg|image:200,200,jpg'])
                ->check($files);
            foreach ($files as $file) {
                $savename = Filesystem::disk('public')->putFile('Recharge', $file);
            }
            $savename = '/storage/' . $savename;
            successAjax('上传成功', $savename);
        } catch (\think\exception\ValidateException $e) {
            echo $e->getMessage();
        }

    }

    public function saveChargeConfig()
    {
        $params = $this->request->param();
        $data['alipay_in'] = $params['alipay'] ?? "";
        $data['wechat_in'] = $params['wechat'] ?? "";
        $data['QQ_in'] = $params['QQ'] ?? "";
        Db::table('config')->where('id', 1)->save($data);
        successAjax('保存成功');
    }

    public function withdrawal()
    {
        return view("/recharge/withdrawal");
    }

    public function charge()
    {
        return view("/recharge/charge");
    }

    public function loadChargeRecord()
    {
        $params = request()->param();
        $params['type'] = 'buy';
        $result = $this->buildRecord($params);
        return json($result);
    }

    public function loadWithdrawalRecord()
    {
        $params = request()->param();
        $params['type'] = 'sell';
        $result = $this->buildRecord($params);
        return json($result);
    }

    private function buildRecord($params)
    {
        $where = [];

        if (!empty($params['username'])) {
            $where[] = ['mr.user_name', 'like', '%' . $params['username'] . '%'];
        }
        if (!empty($params['order'])) {
            $where[] = ['mr.order_no', 'like', '%' . $params['order'] . '%'];
        }

        if (!empty($params['account_type'])) {
            $where[] = ['mr.account_type', '=', $params['account_type']];
        }
        if (!empty($params['status'])) {
            if ($params['status'] == 'none') $params['status'] = 0;
            $where[] = ['mr.status', '=', $params['status']];
        }
        $where[] = ['mr.type', '=', $params['type']];
        $where[] = ['mr.is_del','=',0];
        $where[] = ['wt.is_del','=',0];
        $where[] = ['wt.is_allow','=',1];

        $data = Db::table('money_record')
            ->alias('mr')
            ->leftJoin('withdrawal_type wt', 'mr.user_id = wt.user_id')
            ->where($where)
            ->whereRaw('wt.type = mr.account_type')
            ->field(['wt.account_name', 'wt.account', 'wt.qr_code', 'mr.*'])
            ->page($params['page'], $params['limit'])
            ->select();

        $count = Db::table('money_record')
            ->alias('mr')
            ->leftJoin('withdrawal_type wt', 'mr.user_id = wt.user_id')
            ->where($where)
            ->whereRaw('wt.type = mr.account_type')
            ->count();
        $result = [];
        $result['code'] = 0;
        $result['count'] = $count;
        $result['data'] = $data;
        return $result;
    }

    public function removeById()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax('1', '缺少id');
        }
        $where = [
            'id' => $params['id'],
            'is_del' => 0
        ];
        $data = Db::table('money_record')->where($where)->find();
        if ($data['status'] != 0) {
            failedAjax('1', '订单如果已通过，则无法删除');
        }
        if ($data['type'] == 'sell') {//如果是提现单，则将冻结的对应金额返回用户余额中
            $user = Db::table('user')
                ->lock(true)
                ->where(['id' => $data['user_id'], 'enable' => 1, 'is_del' => 0])
                ->find();
            $user['fcoin'] = bcsub($user['fcoin'], $data['money'], 3);
            $user['coin'] = bcadd($user['coin'], $data['money'], 3);
            Db::table('user')->lock(true)->save($user);
        }
        $data['is_del'] = 1;
        Db::table('money_record')->save($data);
        successAjax('删除成功');
    }

    public function invalidStatus()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax('1', '缺少参数');
        }
        $where = [
            'id' => $params['id'],
            'is_del' => 0
        ];
        $data = Db::table('money_record')->where($where)->find();
        if ($data['status'] != 0) {
            failedAjax('1', '当前订单状态已变化');
        }
        $data['status'] = 2;
        $data['cause'] = $params['cause'] ?? "";

        if ($data['type'] == 'sell') {//如果是提现单，则将冻结的对应金额返回用户余额中
            $user = Db::table('user')
                ->lock(true)
                ->where(['id' => $data['user_id'], 'enable' => 1, 'is_del' => 0])
                ->find();
            $user['fcoin'] = bcsub($user['fcoin'], $data['money'], 3);
            $user['coin'] = bcadd($user['coin'], $data['money'], 3);
            Db::table('user')->lock(true)->save($user);
        }

        Db::table('money_record')->save($data);
        successAjax('操作成功');
    }

    public function chargeRecord()
    {
        $params = request()->param();
        if (empty($params['id'])) {
            failedAjax('1', '缺少参数');
        }
        $where = [
            'id' => $params['id'],
            'status' => 0,
            'is_del' => 0
        ];
        $data = Db::table('money_record')->lock(true)->where($where)->find();
        if (!$data) {
            failedAjax('1', '数据状态已变更');
        }
        $user = Db::table('user')
            ->lock(true)
            ->where(['id' => $data['user_id'], 'enable' => 1, 'is_del' => 0])
            ->find();
        if (!$user) {
            failedAjax('1', '用户账户已不存在或被禁用');
        }
        Db::startTrans();
        try {
            if ($data['type'] == 'buy') {
                $user['coin'] = bcadd($user['coin'], $data['money'], 3);
                $data['current_remain'] = $user['coin'];
            } else {
                $user['fcoin'] = bcsub($user['fcoin'], $data['money'], 3);
                $data['current_remain'] = $user['fcoin'];
            }
            $data['process_time'] = date('Y-m-d H:i:s');
            $data['status'] = 1;
            Db::table('user')->save($user);
            Db::table('money_record')->save($data);
            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            failedAjax('1', '执行时发生错误');
        }
        successAjax('操作成功');
    }

}