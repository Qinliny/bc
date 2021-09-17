<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Init extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // 用户表
        $this->user();
        // 系统用户表
        $this->admin();
        // 彩种表
        $this->game();
        // 彩种配置表
        $this->game_config();
        // 彩种期数、开奖结果表
        $this->lottery();
        //系统配置表
        $this->config();
        // 用户下注表
        $this->order();
        //用户提现方式表
        $this->withdrawal_type();
        //用户充值/提现记录
        $this->money_record();
        //用户实名认证记录表
        $this->user_auth();
        // 资金变动表
        $this->balance_log();
        // 公告
        $this->notice();
    }


    public function user()
    {
        $table = $this->table('user', ['engine' => 'InnoDB', 'collation' => 'utf8_general_ci',
            'comment' => '用户表', 'id' => 'id', 'primary_key' => ['id']]);

        $table->addColumn('account', 'string', ['null' => false, 'signed' => true, 'comment' => '',])
            ->addColumn('name', 'string', ['null' => true, 'comment' => '真实姓名',])
            ->addColumn('nickname', 'string', ['null' => false, 'comment' => '昵称',])
            ->addColumn('pwd', 'string', ['null' => false, 'comment' => '', 'limit' => 60])
            ->addColumn('enable', 'integer', ['null' => true, 'default' => 1, 'comment' => '',])
            ->addColumn('phone', 'string', ['null' => true, 'signed' => true, 'comment' => '',])
            ->addColumn('is_del', 'integer', ['null' => true, 'default' => 0, 'signed' => true, 'comment' => '',])
            ->addColumn('coin_pwd', 'string', ['null' => true, 'signed' => true, 'comment' => '支付密码', 'limit' => 60])
            ->addColumn('type', 'integer', ['null' => true, 'signed' => true, 'default' => 0, 'comment' => '是否代理：0会员，1代理,2总代理,3股东',])
            ->addColumn('register_ip', 'string', ['null' => true, 'signed' => true, 'comment' => '注册时的ip',])
            ->addColumn('register_time', 'datetime', ['null' => true, 'signed' => true, 'comment' => '注册时间',])
            ->addColumn('update_tiem', 'datetime', ['null' => true, 'signed' => true, 'comment' => '修改密码时间',])
            ->addColumn('coin', 'decimal', ['null' => true, 'default' => 0.00, 'signed' => true, 'comment' => '个人资产',])
            ->addColumn('email', 'string', ['null' => true, 'comment' => '',])
            ->addColumn('qq', 'string', ['null' => true, 'comment' => '',])
            ->addColumn('parent_id', 'string', ['null' => true, 'comment' => '上级代理',])
            ->addColumn('root_parent_id', 'string', ['null' => true, 'signed' => true, 'comment' => '总代理',])
            ->create();

        return $table;
    }

    public function admin()
    {
        $table = $this->table('admin', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '管理员表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('username', 'string', ['null' => false, 'signed' => true, 'comment' => "用户名", 'limit' => 30])
            ->addColumn('password', 'string', ['null' => false, 'signed' => true, 'comment' => "密码", 'limit' => 60])
            ->addColumn('status', 'integer', ['null' => false, 'signed' => true, 'comment' => "状态：0启用 1禁用", 'limit' => 1, 'default' => 0])
            ->addColumn('create_time', 'datetime', ['null' => false, 'signed' => true, 'comment' => "创建时间"])
            ->create();

        return $table;
    }

    public function game()
    {
        $table = $this->table('game', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '彩种表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('game_name', 'string', ['null' => false, 'signed' => true, 'comment' => "彩种名称", 'limit' => 30])
            ->addColumn('highest', 'integer', ['null' => false, 'signed' => true, 'comment' => "单期最高下注", 'limit' => 11])
            ->addColumn('interval', 'integer', ['null' => false, 'signed' => true, 'comment' => "开奖间隔时间", 'limit' => 11])
            ->addColumn('forbid_time', 'integer', ['null' => false, 'signed' => true, 'comment' => "开奖前多少分钟禁止下注", 'limit' => 11])
            ->addColumn('status', 'integer', ['null' => false, 'signed' => true, 'comment' => "状态：0启用 1禁用", 'limit' => 1, 'default' => 0])
            ->addColumn('game_type', 'string', ['null' => false, 'signed' => true, 'comment' => "数据什么类型：六合彩、赛车、时时彩", 'limit' => 60])
            ->addColumn('sort', 'integer', ['null' => false, 'signed' => true, 'comment' => "排序", 'limit' => 11, 'default' => 10])
            ->addColumn('create_time', 'datetime', ['null' => false, 'signed' => true, 'comment' => "创建时间"])
            ->create();

        return $table;
    }

    public function game_config()
    {
        $table = $this->table('game_config', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '彩种配置表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('game_id', 'integer', ['null' => false, 'signed' => true, 'comment' => "彩种ID", 'limit' => 11])
            ->addColumn('config', 'text', ['null' => false, 'signed' => true, 'comment' => "配置信息"])
            ->addColumn('status', 'integer', ['null' => false, 'signed' => true, 'comment' => "状态：0启用 1禁用", 'limit' => 1, 'default' => 0])
            ->addColumn('type', 'string', ['null' => false, 'signed' => true, 'comment' => "玩法：A、B、C、D", 'limit' => 5])
            ->addColumn('create_time', 'datetime', ['null' => false, 'signed' => true, 'comment' => "添加时间"])
            ->addColumn('update_time', 'datetime', ['null' => false, 'signed' => true, 'comment' => "修改时间"])
            ->create();

        return $table;
    }

    public function lottery()
    {
        $table = $this->table('lottery', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '彩种期数、开奖结果表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('game_id', 'integer', ['null' => false, 'signed' => true, 'comment' => "彩种ID", 'limit' => 11])
            ->addColumn('periods', 'integer', ['null' => false, 'signed' => true, 'comment' => "期数"])
            ->addColumn('lottery_time', 'datetime', ['null' => false, 'signed' => true, 'comment' => "开奖时间"])
            ->addColumn('end_time', 'datetime', ['null' => false, 'signed' => true, 'comment' => "禁止下注时间"])
            ->addColumn('is_open', 'integer', ['null' => false, 'signed' => true, 'comment' => "是否开盘 0：开盘 1：封盘", 'limit' => 4, 'default' => 0])
            ->addColumn('is_lottery', 'integer', ['null' => false, 'signed' => true, 'comment' => "是否已开奖 0：未开奖 1：开奖中 2：已开奖", 'limit' => 4, 'default' => 0])
            ->addColumn('result', 'string', ['null' => true, 'signed' => true, 'comment' => "开奖结果", 'limit' => 255])
            ->create();

        return $table;
    }

    public function config()
    {
        $table = $this->table('config', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '系统配置表',
            'primary_key' => ['id']
        ]);
        $table->addColumn('alipay_in', 'string', ['null' => true, 'comment' => "支付宝充值二维码"])
            ->addColumn('wechat_in', 'string', ['null' => true, 'comment' => "微信充值二维码"])
            ->addColumn('QQ_in', 'string', ['null' => true, 'comment' => "QQ充值二维码"])
            ->create();

        return $table;
    }

    public function withdrawal_type()
    {
        $table = $this->table('withdrawal_type', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '用户提现类型表',
            'primary_key' => ['id']
        ]);

        $table->addColumn('user_id', 'string', ['null' => false, 'comment' => "用户的id"])
            ->addColumn('type', 'string', ['null' => false, 'comment' => '提现账户类型，alipay,wechat,QQ,bank'])
            ->addColumn('account', 'string', ['null' => true, 'comment' => '账号'])
            ->addColumn('account_name', 'string', ['null' => true, 'comment' => '持卡人姓名(银行卡转账时需要)'])
            ->addColumn('qr_code', 'string', ['null' => false, 'comment' => '提现用的二维码'])
            ->addColumn('add_time', 'datetime', ['null' => false, 'comment' => '申请时间'])
            ->addColumn('process_time', 'datetime', ['null' => true, 'comment' => '处理时间'])
            ->addColumn('is_allow', 'integer', ['null' => false, 'signed' => true, 'comment' => '是否通过审核'])
            ->addColumn('is_del', 'integer', ['null' => false, 'signed' => true, 'comment' => '是否删除'])
            ->create();

        return $table;
    }

    public function order()
    {
        $table = $this->table('order', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '下注订单表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('order_no', 'string', ['null' => false, 'signed' => true, 'comment' => "订单编号", 'limit' => 30])
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => true, 'comment' => "用户id", 'limit' => 11])
            ->addColumn('game_id', 'integer', ['null' => false, 'signed' => true, 'comment' => "游戏id", 'limit' => 11])
            ->addColumn('lottery_id', 'integer', ['null' => false, 'signed' => true, 'comment' => "期数id", 'limit' => 11])
            ->addColumn('type', 'string', ['null' => false, 'signed' => true, 'comment' => "玩法：A、B、C、D", 'limit' => 5])
            ->addColumn('config_type', 'string', ['null' => false, 'signed' => true, 'comment' => "对应的配置项，用于结算读取", 'limit' => 50])
            ->addColumn('clear_method', 'string', ['null' => false, 'signed' => true, 'comment' => "校验是否中间的方法名称", 'limit' => 50])
            ->addColumn('content', 'string', ['null' => false, 'signed' => true, 'comment' => "具体的下注内容，json格式"])
            ->addColumn('money', 'decimal', ['null' => false, 'signed' => true, 'comment' => "下注金额"])
            ->addColumn('is_win', 'integer', ['null' => false, 'signed' => true, 'comment' => "是否中奖 0：否 1：是", 'limit' => 4, 'default' => 0])
            ->addColumn('status', 'integer', ['null' => false, 'signed' => true, 'comment' => "状态", 'limit' => 4, 'default' => 0])
            ->addColumn('win_amount', 'decimal', ['null' => true, 'signed' => true, 'comment' => "中奖后得到的金额"])
            ->addColumn('create_time', 'datetime', ['null' => true, 'signed' => true, 'comment' => "下注时间"])
            ->create();
        return $table;
    }

    public function money_record()
    {
        $table = $this->table('money_record', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '充值/提现 记录',
            'id' => 'id',
            'primary_key' => ['id', 'order_no']
        ]);

        $table->addColumn('order_no', 'string', ['null' => false, 'comment' => "订单编号", 'limit' => 30])
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => true, 'comment' => "用户id", 'limit' => 11])
            ->addColumn('user_name', 'string', ['null' => false, 'comment' => "用户名"])
            ->addColumn('money', 'decimal', ['null' => false, 'signed' => true, 'comment' => "金额"])
            ->addColumn('current_remain', 'decimal', ['null' => true, 'comment' => "当前账户余额"])
            ->addColumn('type', 'string', ['null' => false, 'comment' => "订单类型，buy|sell"])
            ->addColumn('account_type', 'string', ['null' => false, 'comment' => "充值/提现:alipay,wechat,QQ,bank"])
            ->addColumn('add_time', 'datetime', ['null' => false, 'comment' => "充值时间"])
            ->addColumn('process_time', 'datetime', ['null' => true, 'comment' => "处理时间"])
            ->addColumn('status', 'integer', ['null' => false, 'default' => 0, 'signed' => true, 'comment' => "订单状态：0待审核，1已通过，2已作废，3已取消"])
            ->addColumn('cause', 'text', ['null' => true, 'comment' => "原因"])
            ->addColumn('is_del', 'integer', ['null' => false, 'default' => 0, 'comment' => "是否删除"])
            ->create();
        return $table;
    }

    public function user_auth()
    {
        $table = $this->table('user_auth', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '实名认证记录',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('user_id', 'string', ['null' => false, 'comment' => "用户id"])
            ->addColumn('name', 'string', ['null' => false, 'comment' => "用户名"])
            ->addColumn('true_name', 'string', ['null' => false, 'comment' => "真实姓名"])
            ->addColumn('id_card', 'string', ['null' => true, 'comment' => "身份证号"])
            ->addColumn('add_time', 'datetime', ['null' => false, 'comment' => "申请时间"])
            ->addColumn('process_time', 'datetime', ['null' => true, 'comment' => "处理时间"])
            ->addColumn('status', 'integer', ['null' => false,'default' => 0, 'signed' => true, 'comment' => "状态,0未审核，1通过，2驳回"])
            ->addColumn('is_del', 'integer', ['null' => false,'default' => 0, 'signed' => true, 'comment' => "是否删除"])
            ->create();
        return $table;
    }

    public function balance_log() {
        $table = $this->table('balance_log', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '资金记录表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('user_id', 'integer', ['null' => false, 'comment' => "用户id"])
            ->addColumn('money', 'decimal', ['null' => false, 'comment' => "变动的金额"])
            ->addColumn('balance', 'decimal', ['null' => false, 'comment' => "原始余额"])
            ->addColumn('is_increase', 'integer', ['null' => true,'default' => 1,'comment' => "增加或者减少 1增 0减", 'limit'=>2])
            ->addColumn('remark', 'string', ['null' => false,'limit'=>255,'comment' => "备注"])
            ->addColumn('create_time', 'datetime', ['null' => true, 'signed' => true, 'comment' => "创建时间"])
            ->create();
        return $table;
    }

    public function notice() {
        $table = $this->table('balance_log', [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci',
            'comment' => '公告表',
            'id' => 'id',
            'primary_key' => ['id']
        ]);
        $table->addColumn('type', 'string', ['null' => false, 'comment' => "公告类型"])
            ->addColumn('content', 'string', ['null' => false, 'comment' => "公告内容"])
            ->addColumn('create_time', 'datetime', ['null' => true, 'signed' => true, 'comment' => "创建时间"])
            ->create();
        return $table;
    }
}
