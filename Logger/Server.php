<?php

namespace Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Workerman\Worker;

/**
 * Logger/Server.
 *
 * @author 不再迟疑
 */
class Server {
	private $logerlist = array ();
	public $dir = __DIR__;
	/**
	 * 收集日志的级别
	 * @var unknown
	 */
	public $logger_level = Logger::DEBUG;
	/**
	 * 日志默认按天分文件
	 * @var unknown
	 */
	public $logger_name_dataFormat = 'Y-m-d';
	private $stream;
	/**
	 * Construct.
	 *
	 * @param string $ip        	
	 * @param int $port        	
	 */
	public function __construct($address) {
		$worker = new Worker ( "frame://$address" );
		$worker->transport = 'udp';
		$worker->count = 2;
		$worker->name = 'LoggerServer';
		$worker->onMessage = array (
				$this,
				'onMessage' 
		);
		$time = date ( $this->logger_name_dataFormat );
		$this->stream = new StreamHandler ( $this->dir . "/$time.log", $this->logger_level );
		$this->stream->setFormatter ( new LineFormatter () );
	}
	
	/**
	 * onMessage.
	 *
	 * @param TcpConnection $connection        	
	 * @param string $data        	
	 */
	public function onMessage($connection, $data) {
		if (! $data) {
			return;
		}
		$data = unserialize ( $data );
		$time = date ( 'Y-m-d H:i:s' );
		$logger_name = $data ['logger_name'];
		$logger_level = $data ['logger_level'];
		$logger_message = $data ['logger_message'];
		$logger = $this->getLoger ( $logger_name );
		$logger->addRecord ( $logger_level, $logger_message );
		echo "[$time][$logger_name]" . $logger_message . "\n";
	}
	
	private function getLoger($loger_name) {
		if (! array_key_exists ( $loger_name, $this->logerlist )) {			
			$logger = new Logger ( $loger_name );
			$logger->pushHandler ( $this->stream );
			$this->logerlist [$loger_name] = $logger;
		}
		return $this->logerlist [$loger_name];
	}
}
