<?php

namespace Logger;

use Workerman\Lib\Timer;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\UdpConnection;
use Workerman\Protocols\Frame;

/**
 * Logger/Client
 * 
 * @author 不再迟疑
 */
class Client {
	/**
	 * Detailed debug information
	 */
	const DEBUG = 100;
	
	/**
	 * Interesting events
	 *
	 * Examples: User logs in, SQL logs.
	 */
	const INFO = 200;
	
	/**
	 * Uncommon events
	 */
	const NOTICE = 250;
	
	/**
	 * Exceptional occurrences that are not errors
	 *
	 * Examples: Use of deprecated APIs, poor use of an API,
	 * undesirable things that are not necessarily wrong.
	 */
	const WARNING = 300;
	
	/**
	 * Runtime errors
	 */
	const ERROR = 400;
	
	/**
	 * Critical conditions
	 *
	 * Example: Application component unavailable, unexpected exception.
	 */
	const CRITICAL = 500;
	
	/**
	 * Action must be taken immediately
	 *
	 * Example: Entire website down, database unavailable, etc.
	 * This should trigger the SMS alerts and wake you up.
	 */
	const ALERT = 550;
	
	/**
	 * Urgent alert.
	 */
	const EMERGENCY = 600;

	protected static $worker_name = '';
	
	protected static $address = null;
	
	/**
	 * Connect to channel server
	 * 
	 * @param string $ip        	
	 * @param int $port        	
	 * @return void
	 */
	public static function init($address,$worker_name) {
		self::$address = 'udp://'.$address;
		self::$worker_name = $worker_name;
	}

	public static function log($logger_level, $logger_message, $logger_name='') {
		if(empty($logger_name)){
			$logger_name = self::$worker_name;
		}
		$bin_data = Frame::encode(serialize ( array (
				'logger_name' => $logger_name,
				'logger_level' => $logger_level,
				'logger_message' => $logger_message 
		) ));
		return self::sendData(self::$address, $bin_data);
	}
	
	/**
	 * 发送数据给logger系统
	 * @param string $address
	 * @param string $buffer
	 * @return boolean
	 */
	public static function sendData($address, $buffer)
	{
		$socket = stream_socket_client($address);
		if(!$socket)
		{
			return false;
		}
		return stream_socket_sendto($socket, $buffer) == strlen($buffer);
	}
}
