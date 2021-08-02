<?php

// Workerman设置 仅对 php think worker:server 指令有效
use Workerman\Lib\Timer;
return [
    // 扩展自身需要的配置
    'protocol'       => 'websocket', // 协议 支持 tcp udp unix http websocket text
    'host'           => '0.0.0.0', // 监听地址
    'port'           => 23456, // 监听端口
    'socket'         => '', // 完整监听地址
    'context'        => [], // socket 上下文选项
    'worker_class'   => 'app\Server\LotteryWorker', // 自定义Workerman服务类名 支持数组定义多个服务

    // 支持workerman的所有配置参数
    'name'           => 'thinkphp',
    'count'          => 4,
    'daemonize'      => false,
    'pidFile'        => '',

    // 支持事件回调
    // onWorkerStart
    'onWorkerStart'  => function ($worker) {

    },
    // onWorkerReload
    'onWorkerReload' => function ($worker) {

    },
    // onConnect
    'onConnect'      => function ($connection) {
        echo "Hello World";
    },
    // onMessage
    'onMessage'      => function ($connection, $data) {
        $connection->send('receive success');
    },
    // onClose
    'onClose'        => function ($connection) {

    },
    // onError
    'onError'        => function ($connection, $code, $msg) {
        echo "error [ $code ] $msg\n";
    },
];
