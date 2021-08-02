<?php
use \think\facade\Config;

return [
    'gameList'  =>  [
        [
            'type'  =>  '香港六合彩',
            'view'  =>  'games/hkSixLottery',
            'configPath' => 'jsons/HKSixLottery.json'
        ],
        [
            'type'  =>  '澳门六合彩',
            'view'  =>  'games/hkSixLottery',
            'configPath' => 'jsons/HKSixLottery.json'
        ],
        [
            'type'  =>  '极速六合彩',
            'view'  =>  'games/hkSixLottery',
            'configPath' => 'jsons/HKSixLottery.json'
        ],
        [
            'type'  =>  '北京赛车',
            'view'  =>  'games/carLottery',
            'configPath' => 'jsons/CarLottery.json'
        ],
        [
            'type'  =>  '幸运飞艇',
            'view'  =>  'games/carLottery',
            'configPath' => 'jsons/CarLottery.json'
        ],
        [
            'type'  =>  '三分赛车',
            'view'  =>  'games/carLottery',
            'configPath' => 'jsons/CarLottery.json'
        ],
        [
            'type'  =>  '重庆时时彩',
            'view'  =>  'games/everyColor',
            'configPath' => 'jsons/EveryColor.json'
        ]
    ],
    // 获取香港六合彩开奖信息的地址
    'getSixLotteryResultUrl' => 'https://1680660.com/smallSix/findSmallSixInfo.do'
];