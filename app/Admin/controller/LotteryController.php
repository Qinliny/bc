<?php


namespace app\Admin\controller;


use app\Model\GamesDb;
use app\Model\LotteryDb;
use app\Model\UserDb;
use think\exception\ValidateException;
use think\facade\View;

class LotteryController extends BaseController
{
    // 开奖数据
    public function lottery() {
        $param = $this->request->get();
        $page = !empty($param['page']) && $param['page'] > 1 ? $param['page'] : 1;
        $limit = !empty($param['limit']) && $param['limit'] > 1 ? $param['limit'] : 15;
        $list = LotteryDb::getLotteryList($page, $limit);
        View::assign(['list'=>$list->items(), 'count'=>$list->total(), 'page'=>$page, 'limit'=>$limit]);
        return view();
    }

    public function settingLotteryResult() {
        $param = request()->post();
        try {
            $this->validate($param, [
                'gameId'    =>  'require',
                'gameType'  =>  'require',
                'lotteryId' =>  'require'
            ], [
                'gameId.require'    =>  '参数有误',
                'gameType.require'  =>  '参数有误',
                'lotteryId.require' =>  '参数有误'
            ]);
        } catch (ValidateException $e) {
            failedAjax(__LINE__, $e->getMessage());
        }

        // 判断游戏是否存在
        $gameInfo = GamesDb::findGameInfoById($param['gameId']);
        if ($gameInfo === false || empty($gameInfo)) {
            failedAjax(__LINE__, "当前彩种不存在！");
        }

        // 判断期数是否存在
        $lotteryInfo = LotteryDb::findLotteryInfoById($param['lotteryId']);
        if ($lotteryInfo == false || empty($lotteryInfo)) {
            failedAjax(__LINE__, "期数有误");
        }

        // TODO 判断时间的问题
        switch ($param['gameType']) {
            case "香港六合彩":
            case "澳门六合彩":
            case "极速六合彩":
                // 校验设置的结果
                $numberList = [];
                for ($i = 1; $i <= 7; $i++) {
                    $numberList[] = "number".$i;
                }
                $resultList = [];
                foreach ($numberList as $key => $value) {
                    $numbers = $key+1;
                    if (isset($param[$value])) {
                        if (!in_array($param[$value], createSixLotteryNumber())) {
                            failedAjax(__LINE__, "第{$numbers}球设置有误");
                        }
                        $resultList[] = $param[$value];
                    } else {
                        failedAjax(__LINE__, "第{$numbers}球未进行设置");
                    }
                }
                break;
            case "北京赛车":
            case "幸运飞艇":
            case "三分赛车":
                // 校验设置的结果
                $numberList = [];
                for ($i = 1; $i <= 10; $i++) {
                    $numberList[] = "number".$i;
                }
                $resultList = [];
                foreach ($numberList as $key => $value) {
                    $numbers = $key+1;
                    if (isset($param[$value])) {
                        if (!in_array($param[$value], [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
                            failedAjax(__LINE__, "号码{$numbers}设置有误");
                        }
                        $resultList[] = $param[$value];
                    } else {
                        failedAjax(__LINE__, "号码{$numbers}未进行设置");
                    }
                }
                break;
            case "重庆时时彩":
                // 校验设置的结果
                $numberList = [];
                for ($i = 0; $i < 5; $i++) {
                    $numberList[] = "number".$i;
                }
                $resultList = [];
                foreach ($numberList as $key => $value) {
                    $numbers = $key+1;
                    if (isset($param[$value])) {
                        if (!in_array($param[$value], [0, 1, 2, 3, 4, 5, 6, 7, 8, 9])) {
                            failedAjax(__LINE__, "号码{$numbers}设置有误");
                        }
                        $resultList[] = $param[$value];
                    } else {
                        failedAjax(__LINE__, "号码{$numbers}未进行设置");
                    }
                }
                break;
            default:
                failedAjax(__LINE__, "参数有误");
                break;
        }
        // 更改开奖接口
        $res = LotteryDb::updateLotteryInfoById($param['lotteryId'], ['result'=>json_encode($resultList)]);
        if ($res === false) {
            failedAjax(__LINE__, "设置失败");
        }
        successAjax("设置成功");
    }
}