<?php

use think\migration\Seeder;
use think\facade\Db;

class Admin extends Seeder
{
    public function run()
    {
        // faker默认语言是英文会生成英文的数据，在创建实例的时候可以指定为中文
        $faker = Faker\Factory::create('zh_CN');

        // 创建管理员账号
        $this->createAdminsData($faker);

        // 添加彩种信息
        $this->createGameData($faker);

        $this->createGameConfigData();
    }

    protected function createAdminsData($faker) {
        $rows = [];
        $rows[] = [
            'username'  =>  'admin',
            'password'  =>  password_hash("123456", PASSWORD_DEFAULT),
            'status'    =>  0,
            'create_time'   =>  date('Y-m-d H:i:s', time())
        ];
        Db::table('admin')->insertAll($rows);
    }

    protected function createGameData($faker) {
        $gameList = [
            "香港六合彩" => "六合彩", "澳门六合彩" => "六合彩", "极速六合彩" => "六合彩", "台湾六合彩" => "六合彩", "亚洲六合彩" => "六合彩",
            "北京赛车" => "赛车", "幸运飞艇" => "赛车", "三分赛车" => "赛车",  "PK赛车" => "赛车", "新极速飞艇" => "赛车", "超级飞艇" => "赛车",
            "闪电飞艇" => "赛车",
            "重庆时时彩" => "时时彩", "幸运时时彩" => "时时彩", "极速时时彩" => "时时彩", "闪电时时彩" => "时时彩", "腾讯愤愤彩" => "时时彩"
        ];
        $rows = [];
        foreach ($gameList as $key => $value) {
            $rows[] = [
                'game_name' =>  $key,
                'game_type' =>  $value,
                'highest'   =>  200000,
                'interval'   =>  3,
                'forbid_time'   =>  1,
                'status'    =>  0,
                'create_time'   =>  date('Y-m-d H:i:s', time())
            ];
        }
        Db::table('game')->insertAll($rows);
    }

    protected function createGameConfigData() {
        $data = '{"numberConfig":[{"number":"01","color":"red","chineseZodiac":"\u9f20","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"02","color":"red","chineseZodiac":"\u725b","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"03","color":"blue","chineseZodiac":"\u732a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"04","color":"blue","chineseZodiac":"\u72d7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"05","color":"green","chineseZodiac":"\u9e21","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"06","color":"green","chineseZodiac":"\u7334","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"07","color":"red","chineseZodiac":"\u7f8a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"08","color":"red","chineseZodiac":"\u9a6c","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"09","color":"blue","chineseZodiac":"\u86c7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"10","color":"blue","chineseZodiac":"\u9f99","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"11","color":"green","chineseZodiac":"\u5154","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"12","color":"red","chineseZodiac":"\u864e","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"13","color":"red","chineseZodiac":"\u725b","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"14","color":"blue","chineseZodiac":"\u9f20","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"15","color":"blue","chineseZodiac":"\u732a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"16","color":"green","chineseZodiac":"\u72d7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"17","color":"green","chineseZodiac":"\u9e21","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"18","color":"red","chineseZodiac":"\u7334","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"19","color":"red","chineseZodiac":"\u7f8a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"20","color":"blue","chineseZodiac":"\u9a6c","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"21","color":"green","chineseZodiac":"\u86c7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"22","color":"green","chineseZodiac":"\u9f99","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"23","color":"red","chineseZodiac":"\u5154","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"24","color":"red","chineseZodiac":"\u864e","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"25","color":"blue","chineseZodiac":"\u725b","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"26","color":"blue","chineseZodiac":"\u9f20","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"27","color":"green","chineseZodiac":"\u732a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"28","color":"green","chineseZodiac":"\u72d7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"29","color":"red","chineseZodiac":"\u9e21","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"30","color":"red","chineseZodiac":"\u7334","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"31","color":"blue","chineseZodiac":"\u7f8a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"32","color":"green","chineseZodiac":"\u9a6c","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"33","color":"green","chineseZodiac":"\u86c7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"34","color":"red","chineseZodiac":"\u9f99","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"35","color":"red","chineseZodiac":"\u5154","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"36","color":"blue","chineseZodiac":"\u864e","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"37","color":"blue","chineseZodiac":"\u725b","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"38","color":"green","chineseZodiac":"\u9f20","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"39","color":"green","chineseZodiac":"\u732a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"40","color":"red","chineseZodiac":"\u72d7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"41","color":"blue","chineseZodiac":"\u9e21","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"42","color":"blue","chineseZodiac":"\u7334","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"43","color":"green","chineseZodiac":"\u7f8a","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"44","color":"green","chineseZodiac":"\u9a6c","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"45","color":"red","chineseZodiac":"\u86c7","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"46","color":"red","chineseZodiac":"\u9f99","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"47","color":"blue","chineseZodiac":"\u5154","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"48","color":"blue","chineseZodiac":"\u864e","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"},{"number":"49","color":"green","chineseZodiac":"\u725b","odds":"42.30","return":"0.13","singleNoteMin":"1","singleNoteMax":"200000"}]}';
        for ($i = 1; $i < 3; $i++) {
            $installData = [
                'game_id'   =>  $i,
                'config'    =>  $data,
                'type'      =>  'A',
                'status'    =>  0,
                'create_time'   =>  thisTime(),
                'update_time'   =>  thisTime()
            ];
            Db::table("game_config")->insert($installData);
        }
    }
}