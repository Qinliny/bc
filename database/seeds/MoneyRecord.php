<?php

use think\migration\Seeder;

class MoneyRecord extends Seeder
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

        $data = [];
        $data[] = [
            'order_no' => '11223334455',
            'user_id' => '1',
            'user_name' => '西北老汉',
            'account_type' => 'wechat',
            'money' => '120.00',
            'type' => 'buy',
            'add_time' => date('Y-m-d H:i:s'),
        ];
        $data[] = [
            'order_no' => '11223334466',
            'user_id' => '1',
            'user_name' => '西北老汉',
            'account_type' => 'wechat',
            'money' => '110.00',
            'type' => 'sell',
            'add_time' => date('Y-m-d H:i:s'),
        ];

        // \think\facade\Db::table('money_record')->insertAll($data);
    }
}