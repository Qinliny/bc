<?php

namespace app\Server;

use app\Model\BalanceLogDb;
use app\Model\GamesDb;
use app\Model\LotteryDb;
use app\Model\OrderDb;
use app\Model\UserDb;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Log;
use think\worker\Server;
use Workerman\Lib\Timer;


class LotteryWorker extends Server
{
    protected $socket = 'http://127.0.0.1:2346';

    /**
     * 启动时运行
     * @param $connection
     */
    public function onWorkerStart($connection) {
        // 创建彩种期数数据
        $this->createLottery();

        $gameList = GamesDb::getGameList(1, 100);

        foreach ($gameList as $key => $value) {
            // 每秒获取一次是否到开奖时间，如果到开奖时间则修改期数状态, 每一个定时器获取一个游戏
            Timer::add(5, function() use ($value) {
                $info = Cache::store('redis')->get($value['game_name']);
                $lotteryInfo = json_decode($info, true);

                // 判断是否已经到了禁止下注的时间
                $end_time = strtotime($lotteryInfo['end_time']);

                if ($lotteryInfo['is_lottery'] == 0 && time() >= $end_time) {
                    // 修改状态禁止下注
                    LotteryDb::updateLotteryInfoById($lotteryInfo['id'], ['is_lottery'=>1]);
                }

                // 判断是否已经到了开奖时间
                $lottery_time = strtotime($lotteryInfo['lottery_time']);
                if (time() >= $lottery_time) {
                    // 判断是否已经预设了开奖的数据
                    $lotteryData = LotteryDb::findLotteryInfoById($lotteryInfo['id']);
                    $result = [];
                    switch ($value['game_name']) {
                        case "澳门六合彩":
                        case "极速六合彩":
                            // 获取开奖的数据
                            if (empty($lotteryData['result'])) {
                                $result = getSixLotteryResult();
                            } else {
                                $result = json_decode($lotteryData['result'], true);
                            }
                            break;
                        case "北京赛车":
                        case "幸运飞艇":
                        case "三分赛车":
                            if (empty($lotteryData['result'])) {
                                $result = getCarLotteryResult();
                            } else {
                                $result = json_decode($lotteryData['result'], true);
                            }
                            break;
                        case "重庆时时彩":
                            if (empty($lotteryData['result'])) {
                                $result = getEveryColorLResult();
                            } else {
                                $result = json_decode($lotteryData['result'], true);
                            }
                            break;
                        case "香港六合彩":
                            // 香港六合彩的另作处理
                            $result = $this->getSixLotteryResult();
                            break;
                    }

                    // 开奖
                    LotteryDb::updateLotteryInfoById($lotteryInfo['id'], ['is_lottery'=>2,'result'=>json_encode($result)]);

                    // 添加用户订单到redis，异步处理用户下注的结算
                    $orderList = OrderDb::getOrderListByLotteryId($lotteryInfo['id']);
                    if ($orderList) {
                        foreach ($orderList as $key => $order) {
                            Cache::store('redis')->lPush("list", json_encode($order));
                        }
                    }

                    if ($value['game_name'] != "香港六合彩") {
                        // 创建新的期数
                        $this->createLotteryData($value, (int)$lotteryInfo['periods'] + 1);
                    } else {
                        $this->createSixLotteryIssue($value);
                    }
                }
            });
        }

        // 写一个订单处理定时器用于处理用户是否中奖
        Timer::add(2, function(){
            // 获取一组订单数据 一次消费10条订单数据
            $orderList = [];
            for ($i = 0; $i < 10; $i++) {
                $res = Cache::store('redis')->rPop("list");
                if ($res) {
                    $orderList[] = $res;
                } else {
                    break;
                }
            }
            if ($orderList) {
                // 校验是否中奖
                $this->settleAccounts($orderList);
            }
        });
    }

    /**
     * Websocket连接时运行
     * @param $worker
     */
    public function onConnect($worker) {}

    /**
     * 接收到消息时运行
     * @param $worker
     */
    public function onMessage($worker) {}

    /**
     * 关闭时运行
     * @param $worker
     */
    public function onClose($worker) {}

    /**
     * 定时器启动 创建彩种期数彩票
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function createLottery() {
        // 获取所有的游戏
        $gameList = GamesDb::getGameList(1, 100);
        foreach ($gameList->items() as $key => $val) {
            if ($val['game_name'] == '香港六合彩') {
                $res = Db::table('lottery')->where('is_lottery', '<>', 2)->find();
                if (empty($res)) {
                    $this->createSixLotteryIssue($val);
                }
                continue;
            }

            $lotteryInfo = LotteryDb::getLotteryInfoByGameId($val['id']);
            if ($lotteryInfo == false || empty($lotteryInfo)) {
                // 创建期数
                $this->createLotteryData($val);
            } else {
                continue;
            }
        }
    }

    /**
     * 组装创建期数的数据
     * @param $gameInfo     游戏的详细信息
     * @param int $periods  期数
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function createLotteryData($gameInfo, $periods = 202101) {
        $gameInfoData = GamesDb::getGameInfoById($gameInfo['id']);
        // 获取配置的开奖时间
        $lotteryTime = ((int)$gameInfoData['interval'] * 60) + time();
        // 禁止下注时间
        $endTime = $lotteryTime - ((int)$gameInfoData['forbid_time'] * 60);
        // 创建期数信息
        $createData = [
            'game_id'      =>  $gameInfo['id'],
            'periods'      =>  $periods,
            'lottery_time' =>  thisTime($lotteryTime),
            'end_time'     =>  thisTime($endTime),
            'is_open'      =>  0,
            'is_lottery'   =>  0
        ];
        $res = LotteryDb::createLotteryInfo($createData, false, true);
        if ($res == true) {
            $createData['id'] = $res;
            // 添加到redis缓存
            $result = Cache::store('redis')->set($gameInfo['game_name'], json_encode($createData), 0);
            if (!$result) {
                echo "ERROR：Adding to Redis failed" . PHP_EOL;
                die;
            }
        } else {
            echo "ERROR：Failed to add to database" . __LINE__ . PHP_EOL;
            die;
        }
    }

    /**
     * 六合彩创建期数数据
     * @param $gameInfo
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function createSixLotteryIssue($gameInfo) {
        $url = Config::get("game.getSixLotteryResultUrl");
        $jsonResult = curlRequest($url);
        $result = json_decode($jsonResult,true);
        if (isset($result['errorCode']) && $result['errorCode'] == 0){
            // 获取下期开奖时间
            $nextTime = $result['result']['data']['drawTime'];
            // 下一期
            $nextIssue = $result['result']['data']['drawIssue'];
            $lottery_time = strtotime($nextTime);
            $endTime = $lottery_time - 800;
            // 创建期数信息
            $createData = [
                'game_id'      =>  $gameInfo['id'],
                'periods'      =>  $nextIssue,
                'lottery_time' =>  thisTime($lottery_time + 120), // 两分钟后开奖
                'end_time'     =>  thisTime($endTime),
                'is_open'      =>  0,
                'is_lottery'   =>  0
            ];
            $res = LotteryDb::createLotteryInfo($createData, false, true);
            if ($res == true) {
                $createData['id'] = $res;
                // 添加到redis缓存
                $result = Cache::store('redis')->set($gameInfo['game_name'], json_encode($createData), 0);
                if (!$result) {
                    echo "ERROR：Adding to Redis failed" . PHP_EOL;
                    die;
                }
            } else {
                echo "ERROR：Failed to add to database, FILE: " . __FILE__ . PHP_EOL;
                die;
            }
        }
    }

    // 获取香港六合彩开奖数据
    private function getSixLotteryResult() {
        $url = Config::get("game.getSixLotteryResultUrl");
        $jsonResult = curlRequest($url);
        $result = json_decode($jsonResult,true);
        $returnResult = [];
        if (isset($result['errorCode']) && $result['errorCode'] == 0) {
            $lotteryResultStr = $result['result']['data']['preDrawCode'];
            $lotteryResult = explode(',', $lotteryResultStr);
            foreach ($lotteryResult as $key => $value) {
                if($value < 10) {
                    $returnResult[] = "0".$value;
                } else {
                    $returnResult[] = "".$value;
                }
            }
        }
        return $returnResult;
    }

    /**
     * 校验用户是否中奖
     * @param $orderList
     */
    private function settleAccounts($orderList) {
        foreach ($orderList as $key => $value) {
            try {
                $orderInfo = json_decode($value, true);
                // 获取游戏信息
                $gameInfo = GamesDb::getGameInfoById((int)$orderInfo['game_id']);
                // 获取这一期游戏的信息
                $lotteryInfo = LotteryDb::findLotteryInfoById($orderInfo['lottery_id']);
                // 开奖结果
                $result = json_decode($lotteryInfo['result'], true);
                // 用户下注信息
                $buyInfo = $orderInfo['content'];
                // 获取游戏配置
                $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
                // 校验是否中奖
                $lotteryResult = $orderInfo['clear_method']($result, $buyInfo, $config);
                // 中奖修改订单信息
                if ($lotteryResult == true) {
                    $buyResult = json_decode($buyInfo, true);
                    $sumMoney = 0;
                    // 中奖金额结算
                    foreach ($config[$orderInfo['config_type']] as $key => $conf) {
                        // 连码需要做特殊处理
                        $type = $orderInfo['config_type'] == 'joinNumberConfig' ? $buyResult['key'] : $buyResult['value'];
                        if (isset($conf['type']) && $type == $conf['type']) {
                            $sumMoney = $conf['odds'] * $orderInfo['money'];
                            break;
                        }

                        if (isset($conf['number']) && $type == $conf['number']) {
                            $sumMoney = $conf['odds'] * $orderInfo['money'];
                            break;
                        }
                    }
                    // 修改订单信息
                    OrderDb::updateOrderInfoById($orderInfo['id'], ['is_win'=>1, 'status'=>1, 'win_amount'=>$sumMoney]);
                    // 添加资金日志
                    $userInfo = UserDb::findUserInfoById($orderInfo['user_id']);
                    UserDb::updateUserInfoById($orderInfo['user_id'], ['coin'=>$userInfo['coin'] + $sumMoney]);
                    BalanceLogDb::addBalanceLog([
                        'user_id'   =>  $userInfo['id'],
                        'money'     =>  $sumMoney,
                        'balance'   =>  $userInfo['coin'],
                        'is_increase'   =>  1,
                        'remark'    =>  '订单编号：' . $orderInfo['order_no'] . "中奖结算",
                        'create_time'   =>  thisTime()
                    ]);
                } else {
                    OrderDb::updateOrderInfoById($orderInfo['id'], ['status'=>1]);
                }
            } catch (Exception $e) {
                Log::error("校验用户是否中奖错误异常：", $e);
            }
        }
    }
}