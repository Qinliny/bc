<?php
namespace app\Main\controller;


use app\Model\GamesDb;
use app\Model\LotteryDb;
use app\Model\OrderDb;
use app\Model\UserDb;
use think\facade\Config;
use think\facade\View;

class GameController extends BaseController
{
    // 用户下注页面
    public function index() {
        $gameId = $this->request->get('gameId');

        // 获取游戏信息
        $gameInfo = GamesDb::getGameInfoById((int)$gameId);
        if ($gameInfo === false) abort(500, "获取信息失败");

        if (empty($gameInfo)) abort(500, "彩种不存在");

        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);

        // 获取配置信息
        $configs = Config::get('game.gameList');

        if (in_array($gameInfo['game_name'], ['北京赛车', '幸运飞艇', '三分赛车'])) {
            $titleList = [
                'topOrTwoTotal' => '冠、亚总和', 'champion' => '冠军', 'secondPlace' => '亚军', 'third' => '第三名',
                'fourth' => '第四名', 'fifth' => '第五名', 'sixth' => '第六名', 'seventh' => '第七名', 'eighth' => '第八名',
                'ninth' => '第九名',
            ];
            View::assign('titleList', $titleList);
        }

        if ($gameInfo['game_name'] == '重庆时时彩') {
            $titleList = [
                'number1Config' => '第一球', 'number2Config' => '第二球', 'number3Config' => '第三球',
                'number4Config' => '第四球', 'number5Config' => '第五球'
            ];
            View::assign('titleList', $titleList);
        }

        // 获取对应的视图地址
        $viewPath = "";
        foreach ($configs as $key => $val) {
            if ($val['type'] == $gameInfo['game_name']) {
                $viewPath = $val['view'];
            }
        }

        // 获取当前游戏开盘的期数
        $lotteryInfo = LotteryDb::getLotteryInfoByGameId($gameId);

        View::assign(['gameInfo'=>$gameInfo, 'config'=>$config, 'lotteryInfo'=>$lotteryInfo]);

        // 不同的彩种对应不通的前端页面
        return view($viewPath);
    }

    // 香港六合彩下注页面
    public function sixLottery() {
        $type = request()->get('type');
        if (empty($type)) {
            abort(500, "传入参数");
        }
        if (!in_array($type, ['香港六合彩','澳门六合彩','极速六合彩'])) {
            abort(500, "参数有误");
        }

        // 获取游戏信息
        $gameInfo = GamesDb::getGameInfoByName($type);
        if ($gameInfo === false) abort(500, "获取信息失败");

        if (empty($gameInfo)) abort(500, "彩种不存在");

        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        // 特码配置
        $numberList = array_chunk($config['numberConfig'], 5);
        // 色波配置
        $colorNumber = array_chunk($config['colorTypeConfig'],3);
        // 特肖
        $chineseZodiac = array_chunk($config['chineseZodiacConfig'],  2);
        // 两面
        $twoFace = array_chunk($config['twoFaceConfig'],  2);

        // 获取当前游戏开盘的期数
        $lotteryInfo = LotteryDb::getLotteryInfoByGameId($gameInfo['id']);
        // 获取上一期的开奖结果
        $lastLotteryInfo = LotteryDb::getLastLottery($gameInfo['id']);
        if (!empty($lastLotteryInfo)) {
            $lastLotteryInfo['result'] = json_decode($lastLotteryInfo['result'],  true);
        }
        return view('games/hkSixLottery', ['numberList'=>$numberList, 'gameInfo'=>$gameInfo,
            'config'=>$config, 'colorNumber'=>$colorNumber, 'chineseZodiac'=>$chineseZodiac,
            'lotteryInfo'=>$lotteryInfo, 'type'=>$type, 'lastLotteryInfo'=>$lastLotteryInfo,'twoFace'=>$twoFace]);
    }

    // 北京赛车等下注页面
    public function carLottery() {
        $type = request()->get('type');
        if (empty($type)) {
            abort(500, "传入参数");
        }
        if (!in_array($type, ['北京赛车','幸运飞艇','三分赛车'])) {
            abort(500, "参数有误");
        }

        // 获取游戏信息
        $gameInfo = GamesDb::getGameInfoByName($type);
        if ($gameInfo === false) abort(500, "获取信息失败");

        if (empty($gameInfo)) abort(500, "彩种不存在");

        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);

        // 获取当前游戏开盘的期数
        $lotteryInfo = LotteryDb::getLotteryInfoByGameId($gameInfo['id']);
        // 获取上一期的开奖结果
        $lastLotteryInfo = LotteryDb::getLastLottery($gameInfo['id']);
        if (!empty($lastLotteryInfo)) {
            $data = json_decode($lastLotteryInfo['result'],  true);
            $array = [];
            foreach ($data as $key => $val) {
                $compare = count($data)- 1 - $key;
                if ($val > $data[$compare]) {
                    $array[] = [
                        'key'   =>  $val,
                        'value' =>  "龙"
                    ];
                } else {
                    $array[] = [
                        'key'   =>  $val,
                        'value' =>  "虎"
                    ];
                }
            }

            $lastLotteryInfo['result'] = $array;
        }
        return view('games/carLottery', ['gameInfo'=>$gameInfo, 'config'=>$config, 'type'=>$type,
            'lotteryInfo'=>$lotteryInfo, 'lastLotteryInfo'=>$lastLotteryInfo]);
    }

    // 时时彩下注
    public function everyColor() {
        $type = request()->get('type');
        if (empty($type)) {
            abort(500, "传入参数");
        }

        if ($type != '重庆时时彩') {
            abort(500, "参数有误");
        }

        // 获取游戏信息
        $gameInfo = GamesDb::getGameInfoByName($type);
        if ($gameInfo === false) abort(500, "获取信息失败");

        if (empty($gameInfo)) abort(500, "彩种不存在");

        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        // 获取当前游戏开盘的期数
        $lotteryInfo = LotteryDb::getLotteryInfoByGameId($gameInfo['id']);
        // 获取上一期的开奖结果
        $lastLotteryInfo = LotteryDb::getLastLottery($gameInfo['id']);
        if (!empty($lastLotteryInfo)) {
            $lastLotteryInfo['result'] = json_decode($lastLotteryInfo['result'],  true);
        }
        return view('games/everyColor', ['gameInfo'=>$gameInfo, 'config'=>$config, 'type'=>$type,
            'lotteryInfo'=>$lotteryInfo, 'lastLotteryInfo'=>$lastLotteryInfo]);
    }

    // 用户下注入口
    public function saveOrder() {
        $param = $this->request->post();

        $gameId = (int)$param['gameId'];

        // 查询对应的彩种
        $gameInfo = GamesDb::getGameInfoById($gameId);
        if ($gameInfo === false || empty($gameInfo))
            failedAjax(__LINE__, "彩种不存在！");

        // 判断是否已经封盘
        if ($gameInfo['status'] != 0) {
            failedAjax(__LINE__, "当前彩种已封盘！");
        }

        switch ($gameInfo['game_name']) {
            case "香港六合彩":
            case "澳门六合彩":
            case "极速六合彩":
                switch ($param['type']) {
                    case "number":
                        $this->saveNumberOrder($param, $gameInfo);
                        break;
                    case "colorNumber":
                        $this->saveColorNumberOrder($param, $gameInfo);
                        break;
                    case "chineseZodiacNumber":
                        $this->saveChineseZodiacNumberOrder($param, $gameInfo);
                        break;
                    case "joinNumber":
                        $this->saveJoinNumberOrder($param, $gameInfo);
                        break;
                    case "twoFace":
                        $this->saveTwoFaceOrder($param, $gameInfo);
                        break;
                    case "andShaw":
                        $this->saveAndShawOrder($param, $gameInfo);
                        break;
                    default:
                        failedAjax(__LINE__,"下注失败");
                }
                break;
            case "北京赛车":
            case "幸运飞艇":
            case "三分赛车":
                $this->saveCarLotteryOrder($param, $gameInfo);
                break;
            case "重庆时时彩":
                $this->saveEveryColorOrder($param, $gameInfo);
                break;
            default:
                failedAjax(__LINE__,"下注失败");
        }
    }

    /**
     * 判断下注是否对应当前的期数，以及时间
     * @param $lotteryId
     */
    private function checkLotteryTime($lotteryId, $gameId) {
        // 获取期数信息
        $lotteryInfo = LotteryDb::findLotteryInfoById($lotteryId);
        if ($lotteryInfo === false || empty($lotteryInfo)) {
            failedAjax(__LINE__, "当前彩种期数不存在");
        }

        if ($lotteryInfo['game_id'] != $gameId) {
            failedAjax(__LINE__, "下注失败");
        }

        if ($lotteryInfo['is_lottery'] == 1) {
            failedAjax(__LINE__, "开奖中，禁止下注");
        }

        if ($lotteryInfo['is_lottery'] == 2) {
            failedAjax(__LINE__, "已开奖，请下注下一期彩种");
        }

        // 判断是否已经到了禁止下注的时间
        $end_time = strtotime($lotteryInfo['end_time']);
        if (time() >= $end_time) {
            failedAjax(__LINE__, "当前之间禁止下注");
        }
    }

    /**
     * 六合彩特码下注
     * @param $param
     * @param $gameInfo
     */
    private function saveNumberOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];

        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);

        $moneySum = 0;
        foreach ($param['param'] as $key => $value) {
            // 判断当前下注金额是否达到上限
            foreach ($config['numberConfig'] as $k => $conf) {
                if ($conf['number'] == $value['type']) {
                    if ($value['money'] > $conf['singleNoteMax']) {
                        failedAjax(__LINE__, "特码 ".$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                    }
                }
            }
            // 本次下注总金额
            $moneySum += $value['money'];
            $rows[] = [
                // 订单编号
                'order_no'      =>  createOrderNo(),            // 订单编号
                'user_id'       =>  $this->userId,              // 用户ID
                'game_id'       =>  $gameInfo['id'],            // 游戏ID
                'config_type'   =>  "numberConfig",
                'clear_method'  =>  "checkNumber",              // 结算的方法名
                'content'       =>  json_encode(['key'=>'特码', 'value'=>$value['type']]),             // 下注的具体内容
                'money'         =>  $value['money'],            // 金额
                'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                'create_time'   =>  thisTime()                  // 下单时间
            ];
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    /**
     * 六合彩波色下注
     * @param $param
     * @param $gameInfo
     */
    private function saveColorNumberOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];

        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);

        $moneySum = 0;
        foreach ($param['param'] as $key => $value) {
            // 判断当前下注金额是否达到上限
            foreach ($config['colorTypeConfig'] as $k => $conf) {
                if ($conf['type'] == $value['type']) {
                    if ($value['money'] > $conf['singleNoteMax']) {
                        failedAjax(__LINE__, "波色 ".$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                    }
                }
            }
            // 本次下注总金额
            $moneySum += $value['money'];
            $rows[] = [
                // 订单编号
                'order_no'      =>  createOrderNo(),            // 订单编号
                'user_id'       =>  $this->userId,              // 用户ID
                'game_id'       =>  $gameInfo['id'],            // 游戏ID
                'config_type'   =>  "colorTypeConfig",
                'clear_method'  =>  "checkColorNumber",         // 结算的方法名
                'content'       =>  json_encode(['key'=>'波色', 'value'=>$value['type']]),             // 下注的具体内容
                'money'         =>  $value['money'],            // 金额
                'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                'create_time'   =>  thisTime()                  // 下单时间
            ];
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    /**
     * 六合彩生肖下注
     * @param $param
     * @param $gameInfo
     */
    private function saveChineseZodiacNumberOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];
        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);

        $moneySum = 0;
        foreach ($param['param'] as $key => $value) {
            // 判断当前下注金额是否达到上限
            foreach ($config['chineseZodiacConfig'] as $k => $conf) {
                if ($conf['type'] == $value['type']) {
                    if ($value['money'] > $conf['singleNoteMax']) {
                        failedAjax(__LINE__, "特肖 ".$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                    }
                }
            }
            // 本次下注总金额
            $moneySum += $value['money'];
            $rows[] = [
                // 订单编号
                'order_no'      =>  createOrderNo(),            // 订单编号
                'user_id'       =>  $this->userId,              // 用户ID
                'game_id'       =>  $gameInfo['id'],            // 游戏ID
                'config_type'   =>  "chineseZodiacConfig",
                'clear_method'  =>  "checkChineseZodiacNumber", // 结算的方法名
                'content'       =>  json_encode(['key'=>'特肖', 'value'=>$value['type']]), // 下注的具体内容
                'money'         =>  $value['money'],            // 金额
                'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                'create_time'   =>  thisTime()                  // 下单时间
            ];
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    /**
     * 六合彩连码下注
     * @param $param
     * @param $gameInfo
     */
    private function saveJoinNumberOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];
        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);
        $moneySum = 0;
        foreach ($param['param'] as $key => $value) {
            foreach ($config['joinNumberConfig'] as $k => $conf) {
                if ($value['type'] == $conf['type']) {
                    if ($value['money'] > $conf['singleNoteMax']) {
                        failedAjax(__LINE__, "连码 ".$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                    }

                    // 校验输入的数字是否未规定的
                    if (count($value['value']) != $conf['inputNumber']) {
                        failedAjax(__LINE__, $value['type'] . "规定选择" . $conf['inputNumber'] . "个号码！");
                    }

                    // 本次下注总金额
                    $moneySum += $value['money'];

                    $rows[] = [
                        // 订单编号
                        'order_no'      =>  createOrderNo(),            // 订单编号
                        'user_id'       =>  $this->userId,              // 用户ID
                        'game_id'       =>  $gameInfo['id'],            // 游戏ID
                        'config_type'   =>  "joinNumberConfig",
                        'clear_method'  =>  "checkJoinNumber",          // 结算的方法名
                        'content'       =>  json_encode(['key'=>$value['type'], 'value'=>implode(',', $value['value'])]), // 下注的具体内容
                        'money'         =>  $value['money'],            // 金额
                        'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                        'create_time'   =>  thisTime()                  // 下单时间
                    ];
                }
            }
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    private function saveTwoFaceOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];
        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);
        $moneySum = 0;
        foreach ($param['param'] as $key => $value) {
            foreach ($config['twoFaceConfig'] as $k => $conf) {
                if ($value['type'] == $conf['type']) {
                    if ($value['money'] > $conf['singleNoteMax']) {
                        failedAjax(__LINE__, "两面 ".$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                    }

                    // 本次下注总金额
                    $moneySum += $value['money'];

                    $rows[] = [
                        // 订单编号
                        'order_no'      =>  createOrderNo(),            // 订单编号
                        'user_id'       =>  $this->userId,              // 用户ID
                        'game_id'       =>  $gameInfo['id'],            // 游戏ID
                        'config_type'   =>  "twoFaceConfig",            // 配置项名称
                        'clear_method'  =>  "checkTowFace",             // 结算的方法名
                        'content'       =>  json_encode(['key'=>"两面", 'value'=>$value['type']]), // 下注的具体内容
                        'money'         =>  $value['money'],            // 金额
                        'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                        'create_time'   =>  thisTime()                  // 下单时间
                    ];
                }
            }
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    private function saveAndShawOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];
        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);
        $moneySum = 0;

        foreach ($param['param'] as $key => $value) {
            foreach ($config['andShawConfig'] as $k => $conf) {
                if ($value['type'] == $conf['type']) {
                    if ($value['money'] > $conf['singleNoteMax']) {
                        failedAjax(__LINE__, "合肖 ".$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                    }

                    // 本次下注总金额
                    $moneySum += $value['money'];

                    $rows[] = [
                        // 订单编号
                        'order_no'      =>  createOrderNo(),            // 订单编号
                        'user_id'       =>  $this->userId,              // 用户ID
                        'game_id'       =>  $gameInfo['id'],            // 游戏ID
                        'config_type'   =>  "andShawConfig",            // 配置项名称
                        'clear_method'  =>  "checkAndShaw",             // 结算的方法名
                        'content'       =>  json_encode(['key'=>$value['type'], 'value'=>$value['detail']]), // 下注的具体内容
                        'money'         =>  $value['money'],            // 金额
                        'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                        'create_time'   =>  thisTime()                  // 下单时间
                    ];
                }
            }
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    /**
     * 北京赛车、幸运飞艇、三分赛车下注
     * @param $param
     * @param $gameInfo
     */
    public function saveCarLotteryOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];
        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);
        $configType = array_keys($config);
        $moneySum = 0;
        // 校验请求
        foreach ($param['param'] as $key => $value) {
            if (!in_array($value['lottery_type'], $configType)) {
                failedAjax(__LINE__, "下注失败！");
            }
            // 本次下注总金额
            $moneySum += $value['money'];
            // 获取当前的配置
            foreach ($config[$value['lottery_type']] as $k => $conf) {

                if ($value['type'] != $conf['type']) {
                    continue;
                }

                // 下注金额是否达到上限
                if ($value['money'] > $conf['singleNoteMax']) {
                    failedAjax(__LINE__, $value['title'].$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                }

                $rows[] = [
                    // 订单编号
                    'order_no'      =>  createOrderNo(),            // 订单编号
                    'user_id'       =>  $this->userId,              // 用户ID
                    'game_id'       =>  $gameInfo['id'],            // 游戏ID
                    'config_type'   =>  $value['lottery_type'],
                    'clear_method'  =>  "checkCarLottery",          // 结算的方法名
                    'content'       =>  json_encode(['key'=>$value['lottery_type'], 'value'=>$value['type']]), // 下注的具体内容
                    'money'         =>  $value['money'],            // 金额
                    'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                    'create_time'   =>  thisTime()                  // 下单时间
                ];
            }
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }

    /**
     * 重庆时时彩下注
     * @param $param
     * @param $gameInfo
     */
    public function saveEveryColorOrder($param, $gameInfo) {
        // 获取游戏配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);
        $rows = [];
        // 校验期数限制
        $this->checkLotteryTime($param['lotteryId'], $gameInfo['id']);
        $configType = array_keys($config);
        $moneySum = 0;
        // 校验请求
        foreach ($param['param'] as $key => $value) {
            if (!in_array($value['lottery_type'], $configType)) {
                failedAjax(__LINE__, "下注失败！");
            }
            // 本次下注总金额
            $moneySum += $value['money'];
            // 获取当前的配置
            foreach ($config[$value['lottery_type']] as $k => $conf) {
                if ($value['type'] != $conf['type']) {
                    continue;
                }

                // 下注金额是否达到上限
                if ($value['money'] > $conf['singleNoteMax']) {
                    failedAjax(__LINE__, $value['title'].$value['type']."下注金额超过限定的".$conf['singleNoteMax']."，请重新下注！");
                }
                $rows[] = [
                    // 订单编号
                    'order_no'      =>  createOrderNo(),            // 订单编号
                    'user_id'       =>  $this->userId,              // 用户ID
                    'game_id'       =>  $gameInfo['id'],            // 游戏ID
                    'config_type'   =>  $value['lottery_type'],
                    'clear_method'  =>  "checkEveryColor",          // 结算的方法名
                    'content'       =>  json_encode(['key'=>$value['lottery_type'], 'value'=>$value['type']]), // 下注的具体内容
                    'money'         =>  $value['money'],            // 金额
                    'lottery_id'    =>  $param['lotteryId'],        // 期数ID
                    'create_time'   =>  thisTime()                  // 下单时间
                ];
            }
        }

        // 判断用户余额是否足够扣除
        $userInfo = UserDb::findUserInfoById($this->userId);
        if ($userInfo['coin'] < $moneySum) {
            failedAjax(__LINE__, "下注失败，余额不足以扣除本次下注金额!");
        }

        // 获取本期当前用户下注总金额
        $thisLotteryMoneySum = OrderDb::getUserOrderMoneySum($this->userId, $gameInfo['id'], $param['lotteryId']);
        if ($thisLotteryMoneySum + $moneySum > $gameInfo['highest']) {
            failedAjax(__LINE__, "下注失败，下注金额已超过本期限定!");
        }
        $result = OrderDb::createOrder($rows, true);
        if ($result === false) {
            failedAjax(__LINE__, "下注失败！");
        }
        successAjax("下注成功！");
    }
}