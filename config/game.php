<?php
use \think\facade\Config;

return [
    'gameList'  =>  [
        [
            'type'  =>  '六合彩',
            'view'  =>  'games/hkSixLottery',
            'configPath' => 'jsons/HKSixLottery.json'
        ],
        [
            'type'  =>  '赛车',
            'view'  =>  'games/carLottery',
            'configPath' => 'jsons/CarLottery.json'
        ],
        [
            'type'  =>  '时时彩',
            'view'  =>  'games/everyColor',
            'configPath' => 'jsons/EveryColor.json'
        ]
    ],
    // 获取香港六合彩开奖信息的地址
    'getSixLotteryResultUrl' => 'https://1680660.com/smallSix/findSmallSixInfo.do'
];