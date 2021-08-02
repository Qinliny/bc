<?php


namespace app\Api\logic;


class BaseLogic
{
    protected function ok($msg = '操作成功', $data = [])
    {
        return ['code' => 20000, 'msg' => $msg, 'data' => $data];
    }

    protected function bad($msg = '操作失败', $data = [])
    {
        return ['code' => 50012, 'msg' => $msg, 'data' => $data];
    }
}