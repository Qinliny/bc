<?php
namespace app\Admin\controller;

use app\Model\GamesConfigDb;
use app\Model\GamesDb;
use think\App;
use think\exception\ValidateException;
use think\facade\View;
use think\facade\Config;

class GamesController extends BaseController
{
    private $gameList;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $gameList = Config::get("game.gameList");
        $this->gameList = array_column($gameList, 'type');
    }

    // 彩种列表
    public function gamesSettings() {
        $param = $this->request->get();
        $page = isset($param['page']) && $param['page'] > 0 ? $param['page'] : 1;
        $limit = isset($param['limit']) && $param['limit'] > 0 ? $param['limit'] : 15;
        $result = GamesDb::getGameList($page, $limit);
        if ($result === false) {
            abort(500, "获取彩种列表失败");
        }
        return view('games/games_list', ['list'=>$result->items(), 'page'=>$page, 'limit'=>$limit,
            'count'=>$result->total()]);
    }

    // 添加彩种名称
    public function addGame() {
        $param = $this->request->post();
        try {
            $this->validate($param, [
                'name'          =>  'require|max:20',
                'highest'       =>  'require|integer',
                'interval'      =>  'require|integer',
                'forbid_time'   =>  'require|integer',
            ], [
                'name.require'  => '请输入彩种名称',
                'name.max'      => '彩种名称长度不能超过20',
                'highest.require'  => '请输入单期最高可下注金额',
                'highest.integer'  => '单期最高可下注金额必须为正整数',
                'interval.require' => '请输入开奖间隔',
                'interval.integer' => '开奖间隔必须是一个正整数',
                'forbid_time.require' => '请输入禁止下注时间',
                'forbid_time.integer' => '禁止下注时间必须是一个正整数',
            ]);
        } catch ( ValidateException $e) {
            failedAjax($e->getMessage());
        }

        $result = GamesDb::addGame($param);
        if ($result === false) failedAjax(__LINE__,"添加彩种失败！");
        successAjax("添加成功！");
    }

    // 彩种配置页面
    public function gameConfig() {
        $gameId = $this->request->get('gameId');
        // 获取对应的配置 返回对应的视图
        $gameInfo = GamesDb::getGameInfoById((int)$gameId);
        if ($gameInfo === false) abort(500, "获取彩种数据异常！");
        if (empty($gameInfo)) abort(500, "彩种数据不存在！");

        if (!in_array($gameInfo['game_name'], $this->gameList))
            abort(500, "当前彩种不存在，请联系开发者");
        // 获取配置
        $config = returnGameConfig($gameInfo['game_name'], $gameInfo['config']);

        // 获取对应的视图
        $configs = Config::get("game.gameList");
        $viewName = "";
        foreach ($configs as $key => $val) {
            if ($val['type'] == $gameInfo['game_name']) {
                $viewName = $val['view'];
                break;
            }
        }

        View::assign([
            'title'             =>  $gameInfo['game_name'],
            'config'            =>  $config,
            'gameId'            =>  $gameInfo['id']
        ]);

        if (empty($viewName)) abort(500, "获取信息失败！");
        return view($viewName);
    }

    // 保存配置的入口方法
    public function saveGameConfig() {
        $param = $this->request->post();
        if (in_array($param['type'], $this->gameList)) {
            $res = $this->saveLotteryConfig($param['typeItem'], $param['config'], $param['gameId']);
            if ($res != false) {
                successAjax("配置成功！");
            }
        }
        failedAjax(__LINE__, "配置失败！");
    }

    /**
     * 保存修改彩种配置数据
     * @param $type     区分是什么数据
     * @param $config   具体的配置
     * @param $gameId   当前彩种的ID
     */
    private function saveLotteryConfig($type, $config, $gameId) {
        // 获取原来
        $configInfo = GamesConfigDb::getGameConfigInfoByGameId($gameId);
        if ($configInfo === false) abort(500, "获取彩种配置信息失败！");
        $oldConfigInfo = [];
        $isNew = true;
        if (!empty($configInfo)) {
            $isNew = false;
            $oldConfigInfo = json_decode($configInfo['config'], true);
        }
        $data = explodeData($config);
        switch ($type) {
            // 六合彩配置等
            case "numberConfig":
                $oldConfigInfo['numberConfig'] = $data;
                break;
            case "colorTypeConfig":
                $oldConfigInfo['colorTypeConfig'] = $data;
                break;
            case "chineseZodiacConfig":
                $oldConfigInfo['chineseZodiacConfig'] = $data;
                break;
            case "joinNumberConfig":
                $oldConfigInfo['joinNumberConfig'] = $data;
                break;
            case "twoFaceConfig":
                $oldConfigInfo['twoFaceConfig'] = $data;
                break;
            case "andShawConfig":
                $oldConfigInfo['andShawConfig'] = $data;
                break;
            case "headAndEndConfig":
                $oldConfigInfo['headAndEndConfig'] = $data;
                break;
            // 北京赛车、幸运飞艇、三分赛车配置
            case "topOrTwoTotalConfig":
                $oldConfigInfo['topOrTwoTotal'] = $data;
                break;
            case "championConfig":
                $oldConfigInfo['champion'] = $data;
                break;
            case "secondPlaceConfig":
                $oldConfigInfo['secondPlace'] = $data;
                break;
            case "thirdConfig":
                $oldConfigInfo['third'] = $data;
                break;
            case "fourthConfig":
                $oldConfigInfo['fourth'] = $data;
                break;
            case "fifthConfig":
                $oldConfigInfo['fifth'] = $data;
                break;
            case "sixthConfig":
                $oldConfigInfo['sixth'] = $data;
                break;
            case "seventhConfig":
                $oldConfigInfo['seventh'] = $data;
                break;
            case "eighthConfig":
                $oldConfigInfo['eighth'] = $data;
                break;
            case "ninthConfig":
                $oldConfigInfo['ninth'] = $data;
                break;
            case "tenthConfig":
                $oldConfigInfo['tenth'] = $data;
                break;
            // 重庆时时彩
            case "sumConfig":
                $oldConfigInfo['sumConfig'] = $data;
                break;
            case "number1Config":
                $oldConfigInfo['number1Config'] = $data;
                break;
            case "number2Config":
                $oldConfigInfo['number2Config'] = $data;
                break;
            case "number3Config":
                $oldConfigInfo['number3Config'] = $data;
                break;
            case "number4Config":
                $oldConfigInfo['number4Config'] = $data;
                break;
            case "number5Config":
                $oldConfigInfo['number5Config'] = $data;
                break;
            case "top1Config":
                $oldConfigInfo['top1Config'] = $data;
                break;
            case "top2Config":
                $oldConfigInfo['top2Config'] = $data;
                break;
            case "top3Config":
                $oldConfigInfo['top3Config'] = $data;
                break;
            default:
                return false;
        }
        // 判断是修改还是新增
        $res = $isNew ? GamesConfigDb::createGameConfig(json_encode($oldConfigInfo), $gameId):
            GamesConfigDb::updateGameConfig(json_encode($oldConfigInfo), $configInfo['id']);
        if ($res === false)
            abort(500, "配置失败！");
        return true;
    }

    // 删除彩种
    public function delGame() {
        $gameId = $this->request->post('gameId');
        // 获取对应的配置
        $gameInfo = GamesDb::getGameInfoById((int)$gameId);
        // 判断数据是否存在
        if ($gameInfo === false) abort(500, "获取彩种数据异常！");
        if (empty($gameInfo)) abort(500, "彩种数据不存在！");
        // 进行数据删除
        $res = GamesDb::delGameAndConfigByGameId($gameId);
        $res ? successAjax("删除成功！") : failedAjax(__LINE__, "删除失败！");
    }

    // 查询某个彩种的详细配置
    public function findGameInfoById() {
        $gameId = $this->request->post('gameId');
        $gameInfo = GamesDb::getGameInfoById((int)$gameId);
        // 判断数据是否存在
        if ($gameInfo === false) abort(500, "获取彩种数据异常！");
        if (empty($gameInfo)) abort(500, "彩种数据不存在！");
        successAjax("获取成功！", [
            'game_id'   =>  $gameInfo['id'],
            'game_name' =>  $gameInfo['game_name'],
            'highest'   =>  $gameInfo['highest'],
            'interval'  =>  $gameInfo['interval'],
            'forbid_time'   =>  $gameInfo['forbid_time']
        ]);
    }

    // 修改彩种信息
    public function editGameInfoById() {

        $param = $this->request->post();
        $gameId = $param['gameId'];
        $gameInfo = GamesDb::getGameInfoById((int)$gameId);
        // 判断数据是否存在
        if ($gameInfo === false) abort(500, "获取彩种数据异常！");
        if (empty($gameInfo)) abort(500, "彩种数据不存在！");

        if (isset($param['status'])) {
            $editData['status'] = $param['status'];
        } else {
            try {
                $this->validate($param, [
                    'highest'       =>  'require|integer',
                    'interval'      =>  'require|integer',
                    'forbid_time'   =>  'require|integer',
                ], [
                    'highest.require'  => '请输入单期最高可下注金额',
                    'highest.integer'  => '单期最高可下注金额必须为正整数',
                    'interval.require' => '请输入开奖间隔',
                    'interval.integer' => '开奖间隔必须是一个正整数',
                    'forbid_time.require' => '请输入禁止下注时间',
                    'forbid_time.integer' => '禁止下注时间必须是一个正整数',
                ]);
            } catch ( ValidateException $e) {
                failedAjax($e->getMessage());
            }

            $editData = [
                'highest'   =>  $param['highest'],
                'interval'  =>  $param['interval'],
                'forbid_time'   =>  $param['forbid_time']
            ];
        }
        $res = GamesDb::editGameInfoById($gameId, $editData);
        $res ? successAjax("修改成功！") : failedAjax(__LINE__, "修改失败！");
    }
}