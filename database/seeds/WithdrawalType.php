<?php

use think\migration\Seeder;

class WithdrawalType extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $faker = Faker\Factory::create('zh_CN');

        $data = [
            'user_id' => 1,
            'type' => 'wechat',
            'account' => '',
            'account_name' => '',
            'qr_code' => '/img',
            'add_time' => date('Y-m-d H:i:s'),
            'is_allow' => 0,
            'is_del' => 0
        ];
        // \think\facade\Db::table('withdrawal_type')->insert($data);
    }
}