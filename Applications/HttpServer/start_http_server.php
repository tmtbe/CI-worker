<?php
/**
 * Created by PhpStorm.
 * User: tmtbe
 * Date: 16-6-17
 * Time: 下午1:56
 */
use Workerman\Worker;

require_once __DIR__ . '/HttpServer.php';
// timer 进程
$worker = new \Server\HttpServer();

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}
