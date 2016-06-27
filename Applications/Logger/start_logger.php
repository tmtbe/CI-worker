<?php
use \Workerman\Worker;

// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';
require_once __DIR__ . '/../../Logger/Autoloader.php';
$log_server = new \Logger\Server( '0.0.0.0:2207' );

// 如果不是在根目录启动，则运行runAll方法
if (! defined ( 'GLOBAL_START' )) {
	Worker::runAll ();
}

