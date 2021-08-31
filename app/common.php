<?php
/**
 * 获取后台菜单
 * @return array
 */
function getMenuList() : array {
    $fileName = config_path() . "jsons/Menu.json";
    $string = file_get_contents($fileName);
    return json_decode($string, true);
}

/**
 * 获取代理后台菜单
 * @return array
 */
function getAgentMenuList() : array {
    $fileName = config_path() . "jsons/AgentMenu.json";
    $string = file_get_contents($fileName);
    return json_decode($string, true);
}

/**
 * 返回当前时间格式  2021-05-25 01:08:55
 * @return string   当前时间 年月日 时分秒
 */
function thisTime($time = null) : string {
    $time = is_null($time) ? time() : $time;
    return date('Y-m-d H:i:s', $time);
}

/**
 * 失败请求返回的信息
 * @param int $code
 * @param string $messages
 */
function failedAjax(int $code, string $messages) {
    header('Content-Type:application/json');
    $return = [
        'code'    =>  $code,
        'errors'  =>  $messages
    ];
    echo json_encode($return, true);
    die;
}

/**
 * 成功返回的信息
 * @param string $message
 * @param array $data
 */
function successAjax(string $message, $data = []) {
    header('Content-Type:application/json');
    $return = [
        'code'     =>  0,
        'message'  =>  $message,
        'data'     =>  $data
    ];
    echo json_encode($return, true);
    die;
}

function curlRequest($url, $method='get', $data = null, $header = array("content-type: application/json"), $https=true, $timeout = 5){
    $method = strtoupper($method);
    $ch = curl_init();//初始化
    curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
    if($https){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
    }
    if ($method != "GET") {
        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
        }
        if ($method == 'PUT' || strtoupper($method) == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//请求数据
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
    //curl_setopt($ch, CURLOPT_HEADER, false);//设置不需要头信息
    $result = curl_exec($ch);//执行请求
    curl_close($ch);//关闭curl，释放资源
    return $result;
}

/**
 * 获取默认的配置文件
 * @param $type
 * @return mixed
 */
function getDefaultGameConfig($type) {
    $gameList = \think\facade\Config::get('game.gameList');
    $fileName = "";
    foreach ($gameList as $key => $val) {
        if ($val['type'] == $type) {
            $fileName = config_path() . $val['configPath'];
            break;
        }
    }
    $string = file_get_contents($fileName);
    return json_decode($string, true);
}

// 生成六合彩49个号码
function createSixLotteryNumber() {
    $numberList = [];
    for ($i = 1; $i <= 49; $i++) {
        if ($i < 10) {
            $numberList[] = "0".$i;
        } else {
            $numberList[] = "".$i;
        }
    }
    return $numberList;
}

/**
 * 检查配置项是否健全，否则补上
 * @param $type
 * @param $config
 */
function returnGameConfig($type, $config) {
    $config = json_decode($config, true);
    // 获取默认配置
    $defaultConfig = getDefaultGameConfig($type);
    switch ($type) {
        case "香港六合彩":
        case "澳门六合彩":
        case "极速六合彩":
            $configTypeList = [
                'numberConfig', 'colorTypeConfig', 'chineseZodiacConfig', 'joinNumberConfig', "twoFaceConfig", "andShawConfig",
                "headAndEndConfig", "orthoCodeConfig", "orthoTemaConfig"
            ];
            foreach ($configTypeList as $key => $value) {
                // 没有的配置项进行默认补全
                if (!isset($config[$value])) {
                    // 特码需要做特殊处理
                    if ($value == 'numberConfig' || $value == 'orthoCodeConfig') {
                        $config[$value] = getDefaultNumberConfig($defaultConfig);
                    } elseif ($value == 'orthoTemaConfig') {
                        $config[$value] = getDefaultNumberOrthoTemaConfig($defaultConfig);
                    } else {
                        $config[$value] = $defaultConfig[$value];
                    }
                }
            }
            break;
        case "北京赛车":
        case "幸运飞艇":
        case "三分赛车":
            $configTypeList = [
                'topOrTwoTotal', 'champion', 'secondPlace', 'third', 'fourth', 'fifth', 'sixth', 'seventh',
                'eighth', 'ninth', 'tenth'
            ];
            foreach ($configTypeList as $key => $value) {
                // 没有的配置项进行默认补全
                if (!isset($config[$value])) {
                    $config[$value] = $defaultConfig[$value];
                }
            }
            break;
        case "重庆时时彩":
            $configTypeList = [
                'sumConfig', 'number1Config', 'number2Config', 'number3Config', 'number4Config', 'number5Config',
                'top1Config', 'top2Config', 'top3Config'
            ];
            foreach ($configTypeList as $key => $value )  {
                if (!isset($config[$value])) {
                    if ($value == 'sumConfig') {
                        $config['sumConfig'] = $defaultConfig['total'];
                    } else if (in_array($value, ['number1Config', 'number2Config', 'number3Config', 'number4Config',
                        'number5Config'])) {
                        // 添加大小单双这些配置
                        $config[$value] = $defaultConfig['number']['type'];
                        // 循环球
                        for ($j = 0; $j <= 9; $j++) {
                            $config[$value][] = [
                                'type'          =>  $j,
                                'odds'          =>  $defaultConfig['number']['number']['odds'],
                                'return'        =>  $defaultConfig['number']['number']['return'],
                                'singleNoteMin' =>  $defaultConfig['number']['number']['singleNoteMin'],
                                'singleNoteMax' =>  $defaultConfig['number']['number']['singleNoteMax']
                            ];
                        }
                    } else {
                        $config[$value] = $defaultConfig['top'];
                    }
                }
            }
            break;
    }
    return $config;
}

/**
 * 解析配置项
 * @param $config       配置信息
 * @param bool $isSort  是否排序
 * @return array        返回数据
 */
function explodeData($config, $isSort = false) {
    $saveData = [];
    foreach ($config as $key => $value) {
        $data = explode('-', $key);
        $saveData[$data[1]][$data['0']] = $value;
    }
    if ($isSort) ksort($saveData);
    return array_values($saveData);
}

/**
 * 获取默认的特码
 * @return array|bool
 */
function getDefaultNumberConfig($defaultConfig) {
    $numberConfig = [];
    // 特码配置
    for ($i = $defaultConfig['numberConfig']['beginNumber']; $i <= $defaultConfig['numberConfig']['endNumber']; $i++) {
        $number = $i < 10 ? "0".$i : $i;
        $numberConfig[] = [
            "number"        =>  $number,                                         // 号码
            "color"         =>  "",                                              // 颜色：red、blue、green
            "chineseZodiac" =>  "",                                              // 生肖
            "odds"          =>  $defaultConfig['numberConfig']['odds'],          // 赔率
            "return"        =>  $defaultConfig['numberConfig']['return'],        // 退水
            "singleNoteMin" =>  $defaultConfig['numberConfig']['singleNoteMin'], // 单注最低
            "singleNoteMax" =>  $defaultConfig['numberConfig']['singleNoteMax'], // 单注最高
        ];
    }
    return $numberConfig;
}

function getDefaultNumberOrthoTemaConfig($defaultConfig) {
    $numberConfig = [];
    for ($j = 1; $j < 7; $j++) {
        $list = [];
        // 特码配置
        for ($i = $defaultConfig['numberConfig']['beginNumber']; $i <= $defaultConfig['numberConfig']['endNumber']; $i++) {
            $number = $i < 10 ? "0".$i : $i;
            $list[] = [
                "number"        =>  $number,                                         // 号码
                "color"         =>  "",                                              // 颜色：red、blue、green
                "chineseZodiac" =>  "",                                              // 生肖
                "odds"          =>  $defaultConfig['numberConfig']['odds'],          // 赔率
                "return"        =>  $defaultConfig['numberConfig']['return'],        // 退水
                "singleNoteMin" =>  $defaultConfig['numberConfig']['singleNoteMin'], // 单注最低
                "singleNoteMax" =>  $defaultConfig['numberConfig']['singleNoteMax'], // 单注最高
            ];
        }
        $numberConfig["orthoTema{$j}"] = $list;
    }

    return $numberConfig;
}

/**
 * 生成订单编号
 * @return string
 */
function createOrderNo() {
    return date('Ymd').substr(time(), -5) . substr(microtime(), 2, 5) .
        sprintf('%02d', rand(1000, 9999));
}

/**
 * 返回六合彩开奖结果
 * @return array
 */
function getSixLotteryResult() {
    $numberArr = [];
    $isWhile = true;
    while ($isWhile) {
        if (count($numberArr) == 7) {
            $isWhile = false;
            break;
        }
        $number = rand(1, 49);
        if ($number < 10) $number = "0".$number;

        if (!in_array($number, $numberArr)) {
            $numberArr[] = (string)$number;
        }
    }
    return $numberArr;
}

/**
 * 返回北京赛车等结果
 * @return array
 */
function getCarLotteryResult() {
    $numberArr = [];
    $isWhile = true;
    while ($isWhile) {
        if (count($numberArr) == 10) {
            $isWhile = false;
            break;
        }
        $number = rand(1, 10);
        if (!in_array($number, $numberArr)) {
            $numberArr[] = $number;
        }
    }
    return $numberArr;
}

/**
 * 返回重庆时时彩开奖结果
 * @return array
 */
function getEveryColorLResult() {
    $numberArr = [];
    $isWhile = true;
    while ($isWhile) {
        if (count($numberArr) == 5) {
            $isWhile = false;
            break;
        }
        $number = rand(0, 9);
        $numberArr[] = $number;
    }
    return $numberArr;
}

/**
 * 获取颜色的号码
 * @param $config
 * @param $type
 * @return array
 */
function getColorNumber($config, $type) {
    $numberList = [];
    // 获取所有红波的号码
    foreach ($config['numberConfig'] as $key => $value) {
        if ($value['color'] == $type) {
            $numberList[] = $value['number'];
        }
    }
    return $numberList;
}

/**
 * 获取大小单双的号码
 * @param $numberList
 * @param $type
 */
function getSizeSingleOrDoubleNumberList($numberList, $type) {
    $resultList = [];
    foreach ($numberList as $key => $value) {
        switch ($type) {
            case "大":
                $where = (int)$value >= 25;
                break;
            case "小":
                $where = (int)$value < 25;
                break;
            case "单":
                $where = (int)$value % 2 != 0;
                break;
            case "双":
                $where = (int)$value % 2 == 0;
                break;
            case "大单":
                $where = (int)$value >= 25 && (int)$value % 2 != 0;
                break;
            case "大双":
                $where = (int)$value >= 25 && (int)$value % 2 == 0;
                break;
            case "小单":
                $where = (int)$value < 25 && (int)$value % 2 != 0;
                break;
            case "小双":
                $where = (int)$value < 25 && (int)$value % 2 == 0;
                break;
            default:
                $where = false;
                break;
        }

        if ($where) $resultList[] = $value;
    }
    return $resultList;
}


/**
 * 获取当前生肖的号码
 * @param $config
 * @param $type
 * @return array
 */
function getChineseZodiacNumberList($config, $type) {
    $numberList = [];
    // 获取当前生肖的所有号码
    foreach ($config['numberConfig'] as $key => $value) {
        if ($value['chineseZodiac'] == $type) {
            $numberList[] = $value['number'];
        }
    }
    return $numberList;
}

/**
 * 校验号码的大小单双
 * @param $number   号码
 * @param $type     类型
 */
function checkNumberType($number, $type) {
    switch ($type) {
        case "大":
            $where = (int)$number >= 25;
            break;
        case "小":
            $where = (int)$number <= 24;
            break;
        case "单":
            $where = (int)$number % 2 != 0;
            break;
        case "双":
            $where = (int)$number % 2 == 0;
            break;
        default:
            $where = false;
            break;
    }
    return $where;
}

/**
 * 六合彩特码开奖结果校验
 * @param $result   开奖结果
 * @param $item     当前购买的特码 ["key"=>"特码", "value"=>01]
 * @param $config   配置项
 * @return bool     中奖返回 true 否则返回 false
 */
function checkNumber($result, $item, $config = null) {
    // 假设result是一个数组，获取result最后一个结果与购买的对比
    $lotteryResults = end($result);
    $itemResult = json_decode($item, true);
    return $lotteryResults == $itemResult['value'];
}

/**
 * 色波校验
 * @param $result
 * @param $item     ["key"=>"波色", "value"=>”红波“]
 * @param null $config
 * @return bool
 */
function checkColorNumber($result, $item, $config = null) {
    // 假设result是一个数组，获取result最后一个结果与购买的对比
    $lotteryResults = end($result);
    $itemResult = json_decode($item, true);
    switch ($itemResult['value']) {
        case "红波":
            $numberList = getColorNumber($config, "red");
            break;
        case "蓝波":
            $numberList = getColorNumber($config, "blue");
            break;
        case "绿波":
            $numberList = getColorNumber($config, "green");
            break;
        case "红单":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "单");
            break;
        case "红双":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "双");
            break;
        case "红大":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大");
            break;
        case "红小":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小");
            break;
        case "红大单":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大单");
            break;
        case "红小单":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小单");
            break;
        case "红大双":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大双");
            break;
        case "红小双":
            $colorNumberList = getColorNumber($config, "red");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小双");
            break;
        case "蓝单":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "单");
            break;
        case "蓝双":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "双");
            break;
        case "蓝大":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大");
            break;
        case "蓝小":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小");
            break;
        case "蓝大单":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大单");
            break;
        case "蓝小单":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小单");
            break;
        case "蓝大双":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大双");
            break;
        case "蓝小双":
            $colorNumberList = getColorNumber($config, "blue");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小双");
            break;
        case "绿单":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "单");
            break;
        case "绿双":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "双");
            break;
        case "绿大":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大");
            break;
        case "绿小":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小");
            break;
        case "绿大单":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大单");
            break;
        case "绿小单":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小单");
            break;
        case "绿大双":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "大双");
            break;
        case "绿小双":
            $colorNumberList = getColorNumber($config, "green");
            $numberList = getSizeSingleOrDoubleNumberList($colorNumberList, "小双");
            break;
        default:
            $numberList = [];
            break;
    }

    return in_array($lotteryResults, $numberList);
}

/**
 * 生肖校验
 * @param $result
 * @param $item         ["key"=>"生肖", "value"=>"虎"]
 * @param null $config
 * @return bool
 */
function checkChineseZodiacNumber($result, $item, $config = null) {
    // 假设result是一个数组，获取result最后一个结果与购买的对比
    $lotteryResults = end($result);
    $itemResult = json_decode($item, true);
    $numberList = getChineseZodiacNumberList($config, $itemResult['value']);
    return in_array($lotteryResults, $numberList);
}

/**
 * 校验连串
 * @param $result
 * @param $item      ["key"=>"三种二中二", "value"=>"01,02,03"]
 * @param null $config
 * @return bool
 */
function checkJoinNumber($result, $item, $config = null) {
    $itemResult = json_decode($item, true);
    switch ($itemResult['key']) {
        // 选择三个号码，中两个正码即视为中奖
        case "三中二中二":
            // 用户下注的三个号码
            array_pop($result); // 去除最后一个，最后一个是特码
            $userResult = explode(",", $itemResult['value']);
            $numberList = array_intersect($result, $userResult);
            return count($numberList) >= 2;
        // 选择三个号码，中三个正码即视为中奖，下面的规则一样，赔率不一样
        case "三全中":
        case "三中二中三":
            array_pop($result); // 去除最后一个，最后一个是特码
            // 用户下注的三个号码
            $userResult = explode(",", $itemResult['value']);
            $numberList = array_intersect($result, $userResult);
            return count($numberList) >= 3;
        // 选择两个号码，中两个正码即视为中奖
        case "二全中":
            array_pop($result); // 去除最后一个，最后一个是特码
            $userResult = explode(",", $itemResult['value']);
            $numberList = array_intersect($result, $userResult);
            return count($numberList) >= 2;
        // 选择四个号码，中四个正码即视为中奖
        case "四全中":
            array_pop($result); // 去除最后一个，最后一个是特码
            $userResult = explode(",", $itemResult['value']);
            $numberList = array_intersect($result, $userResult);
            return count($numberList) >= 4;
        // 特串选2个，两个正码中了也不算中奖，一定要有一个特马和一个正码中才算中奖，和二中特中特规则一样
        case "特串":
        case "二中特中特":
            $userResult = explode(",", $itemResult['value']);
            // 特码
            $itemResult = array_pop($result);
            if (
                ( $userResult[1] == $itemResult && in_array($userResult[0], $result) ) ||
                ( $userResult[0] == $itemResult && in_array($userResult[1], $result) )
            ) {
                return true;
            } else {
                return false;
            }
        // 二中特中二，选两个号码， 正码必须中两个
        case "二中特中二":
            $userResult = explode(",", $itemResult['value']);
            array_pop($result);
            $numberList = array_intersect($result, $userResult);
            return $numberList >= 2;
        default:
            return false;
    }
}

/**
 * 校验两面
 * @param $result
 * @param $item ["key"=>"两面", "value"=>"特大"]
 * @param null $config
 */
function checkTowFace($result, $item, $config = null) {
    $itemResult = json_decode($item, true);
    // 特码
    $lotteryResults = end($result);
    switch ($itemResult['value']){
        case "特单":
            return checkNumberType($lotteryResults, "单");
            break;
        case "特双":
            return checkNumberType($lotteryResults, "双");
            break;
        case "特大":
            return checkNumberType($lotteryResults, "大");
            break;
        case "特小":
            return checkNumberType($lotteryResults, "小");
            break;
        case "特合单":
            // 获取号码的个位数和十位数
            $number1 = substr($lotteryResults, 0, 1);
            $number2 = substr($lotteryResults, 1, 1);
            return ($number1+$number2) % 2 != 0;
            break;
        case "特合双":
            $number1 = substr($lotteryResults, 0, 1);
            $number2 = substr($lotteryResults, 1, 1);
            return ($number1+$number2) % 2 == 0;
            break;
        case "特合大":
            $number1 = substr($lotteryResults, 0, 1);
            $number2 = substr($lotteryResults, 1, 1);
            return ($number1+$number2) >= 7 || ($number1+$number2) <= 12;
            break;
        case "特合小":
            $number1 = substr($lotteryResults, 0, 1);
            $number2 = substr($lotteryResults, 1, 1);
            return ($number1+$number2) >= 1 || ($number1+$number2) <= 6;
            break;
        case "特家禽":
            // 家禽的的生肖为牛、马、羊、鸡、狗
            $list = ["牛", "马", "羊", "鸡", "狗", "猪"];
            $numberList = [];
            foreach ($list as $key => $value) {
                $number = getChineseZodiacNumberList($config, $value);
                array_merge($numberList, $number);
            }
            return in_array($lotteryResults, $numberList);
            break;
        case "特野兽":
            // 野兽的的生肖为牛、马、羊、鸡、狗
            $list = ["鼠", "虎", "龙", "蛇", "兔", "猴"];
            $numberList = [];
            foreach ($list as $key => $value) {
                $number = getChineseZodiacNumberList($config, $value);
                array_merge($numberList, $number);
            }
            return in_array($lotteryResults, $numberList);
            break;
        case "特小尾":
            $number2 = substr($lotteryResults, 1, 1);
            return (int)$number2 >= 0 || (int)$number2 <= 4;
            break;
        case "特大尾":
            $number2 = substr($lotteryResults, 1, 1);
            return (int)$number2 >= 5 || (int)$number2 <= 9;
            break;
        case "总和单":
            $numberSum = 0;
            foreach ($result as $key => $value) {
                $numberSum += (int)$value;
            }
            return $numberSum % 2 != 0;
            break;
        case "总和双":
            $numberSum = 0;
            foreach ($result as $key => $value) {
                $numberSum += (int)$value;
            }
            return $numberSum % 2 == 0;
            break;
        case "总和大":
            $numberSum = 0;
            foreach ($result as $key => $value) {
                $numberSum += (int)$value;
            }
            return $numberSum >= 175;
            break;
        case "总和小":
            $numberSum = 0;
            foreach ($result as $key => $value) {
                $numberSum += (int)$value;
            }
            return $numberSum <= 174;
            break;
        case "特大单":
            $numberList = ["25","27","29","31", "33", "35", "37", "39", "41", "43", "45", "47", "49"];
            return in_array($lotteryResults, $numberList);
            break;
        case "特大双":
            $numberList = ["26", "28", "30", "32", "34", "36", "38", "40", "42", "44", "46", "48"];
            return in_array($lotteryResults, $numberList);
            break;
        case "特小单":
            $numberList = ["01", "03", "05", "07", "09", "11", "13", "15", "17", "19", "21", "23"];
            return in_array($lotteryResults, $numberList);
            break;
        case "特小双":
            $numberList = ["02", "04", "06", "08", "10", "12", "14", "16", "18", "20", "22", "24"];
            return in_array($lotteryResults, $numberList);
            break;
        default:
            return false;
    }
}

/**
 * 校验合肖
 * @param $result   开奖结果
 * @param $item ["key"=>"二肖", "value"=>"鼠,虎"]
 * @param null $config
 */
function checkAndShaw($result, $item, $config = null) {
    $itemResult = json_decode($item, true);
    // 特码
    $lotteryResults = end($result);
    // 解析生肖
    $sxList = implode(',', $item);
    $numberList = [];
    foreach ($sxList as $key => $value) {
        foreach ($sxList as $key => $value) {
            $number = getChineseZodiacNumberList($config, $value);
            array_merge($numberList, $number);
        }
    }
    return in_array($lotteryResults, $numberList);
}

/**
 * 校验特头尾
 * @param $result   开奖结果
 * @param $item     ["key"=>"特头尾", "value"=>"0头"]
 * @param null $config
 */
function checkHeadAndEnd($result, $item, $config = null) {
    $itemResult = json_decode($item, true);
    // 特码
    $lotteryResults = end($result);
    $number1 = substr($lotteryResults, 0, 1);
    $number2 = substr($lotteryResults, 1, 1);
    switch ($itemResult['value']) {
        case "0头":
            return $number1 == "0";
        case "1头":
            return $number1 == "1";
        case "2头":
            return $number1 == "2";
        case "3头":
            return $number1 == "3";
        case "4头":
            return $number1 == "4";
        case "0尾":
            return $number2 == "0";
        case "1尾":
            return $number2 == "1";
        case "2尾":
            return $number2 == "2";
        case "3尾":
            return $number2 == "3";
        case "4尾":
            return $number2 == "4";
        case "5尾":
            return $number2 == "5";
        case "6尾":
            return $number2 == "6";
        case "7尾":
            return $number2 == "7";
        case "8尾":
            return $number2 == "8";
        case "9尾":
            return $number2 == "9";
        default:
            return false;
    }
}

/**
 * 北京赛车、幸运飞艇、三分赛车开奖校验
 * @param $result [1,2,3,4,5,6,7,8,9,10]
 * @param $item ["key"=>"topOrTwoTotal", "冠亚大"]
 * @param null $config
 * @return bool
 */
function checkCarLottery($result, $item, $config = null) {
    $itemResult = json_decode($item, true);
    $lotteryResult = false;
    if ($itemResult['key'] == "topOrTwoTotal") {
        // 取开头两个元素进行相加
        $totalSum = $result[0] + $result[1];
        switch ($itemResult['value']) {
            case "冠亚大":
                if ($totalSum > 11) {
                    $lotteryResult = true;
                }
                break;
            case "冠亚小":
                if ($totalSum < 11) {
                    $lotteryResult = true;
                }
                break;
            case "冠亚单":
                if ($totalSum % 2 != 0) {
                    $lotteryResult = true;
                }
                break;
            case "冠亚双":
                if ($totalSum % 2 == 0) {
                    $lotteryResult = true;
                }
                break;
        }
    } else {
        $typeNumber = [
            'champion' => 0, 'secondPlace' => 1, 'third'  => 2, 'fourth' => 3, 'fifth' => 4,
            'sixth' => 5, 'seventh' => 6, 'eighth' => 7, 'ninth' => 8, 'tenth' => 9
        ];
        $number = $typeNumber[$itemResult['key']];
        switch ($itemResult['value']) {
            case "大":
                if ($result[$number] > 5) $lotteryResult = true;
                break;
            case "小":
                if ($result[$number] <= 5) $lotteryResult = true;
                break;
            case "单":
                if ($result[$number] % 2 != 0) $lotteryResult = true;
                break;
            case "双":
                if ($result[$number] % 2 == 0) $lotteryResult = true;
                break;
        }
    }
    return $lotteryResult;
}

/**
 * 重庆时时彩开奖校验
 * @param $result   [0, 1, 5, 9, 0]
 * @param $item     ["key"=>"sumConfig","value"=>"大"]
 * @param null $config
 * @return bool
 */
function checkEveryColor($result, $item, $config = null) {
    $itemResult = json_decode($item, true);
    $lotteryResult = false;

    if ($itemResult['key'] == "sumConfig") {
        // 获取5个球的总和
        $totalSum = 0;
        foreach ($result as $k => $v) {$totalSum += $v;}
        switch ($itemResult['value']) {
            case "总和大":
                if ($totalSum >= 23) {
                    $lotteryResult = true;
                }
                break;
            case "总和小":
                if ($totalSum < 23) {
                    $lotteryResult = true;
                }
                break;
            case "总和单":
                if ($totalSum % 2 != 0) {
                    $lotteryResult = true;
                }
                break;
            case "总和双":
                if ($totalSum % 2 == 0) {
                    $lotteryResult = true;
                }
                break;
        }
    } else {
        $typeNumber = [
            'number1Config' => 0, 'number2Config' => 1, 'number3Config'  => 2, 'number4Config' => 3,
            'number5Config' => 4
        ];
        $number = $typeNumber[$itemResult['key']];
        switch ($itemResult['value']) {
            case "大":
                if ($result[$number] > 4) $lotteryResult = true;
                break;
            case "小":
                if ($result[$number] <= 4) $lotteryResult = true;
                break;
            case "单":
                if ($result[$number] % 2 != 0) $lotteryResult = true;
                break;
            case "双":
                if ($result[$number] % 2 == 0) $lotteryResult = true;
                break;
            case "0":
            case "1":
            case "2":
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
            case "8":
            case "9":
                if ($result[$number] == (int)$itemResult['value']) {
                    $lotteryResult = true;
                }
        }
    }
    return $lotteryResult;
}
