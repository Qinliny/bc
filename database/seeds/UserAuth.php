<?php

use think\migration\Seeder;

class UserAuth extends Seeder
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
            'name' => '西北老汉',
            'true_name' => '西北',
            'id_card' => '112233445555555',
            'add_time' => date('Y-m-d H:i:s'),
            'process_time' => date('Y-m-d H:i:s'),
            'status' => 0,
            'is_del' => 0
        ];
        // \think\facade\Db::table('user_auth')->insert($data);
    }
}