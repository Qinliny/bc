<?php

use think\migration\Seeder;
use think\facade\Db;

class Config extends Seeder
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
        $data['alipay_in'] = '';
        $data['wechat_in'] = '';
        $data['QQ_in'] = '';

        Db::table('config')->insert($data);
    }
}