<?php

use think\migration\Seeder;
use think\facade\Db;

class User extends Seeder
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
        // faker默认语言是英文会生成英文的数据，在创建实例的时候可以指定为中文
        $faker = Faker\Factory::create('zh_CN');

        $data = [
            'account' => 'hh1336',
            'name' => '张三',
            'nickname' => '西北老汉',
            'pwd' => password_hash('123456', PASSWORD_DEFAULT),
            'phone' => 13800000001,
            'coin_pwd' => password_hash('123456', PASSWORD_DEFAULT),
            'type' => 2,
            'coin' => 1888888.4455,
            'fcoin' => 110.00,
            'score' => 1000000,
            'deposit' => 1000000
        ];

        // Db::table('user')->insert($data);
    }
}