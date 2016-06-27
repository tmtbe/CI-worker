<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT	MIT License
 * @link    https://codeigniter.com
 * @since    Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logging Class
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Logging
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/general/errors.html
 */
class CI_Log
{

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

    // --------------------------------------------------------------------
    protected $_log_name = '';

    protected $_log_udp_address = null;

    protected $_log_att=['debug'=>CI_Log::DEBUG,
        'info'=>CI_Log::INFO,
        'notice'=>CI_Log::NOTICE,
        'warning'=>CI_Log::WARNING,
        'error'=>CI_Log::ERROR,
        'critical'=>CI_Log::CRITICAL,
        'alert'=>CI_Log::ALERT
    ];
    /**
     * Class constructor
     *
     * @return    void
     */
    public function __construct()
    {
        $config =& get_config();
        $this->_log_name = (isset($config['log_name']) && $config['log_name'] !== '') ? $config['log_name'] : 'CI';
        $this->_log_udp_address = (isset($config['log_udp_address']) && $config['log_udp_address'] !== '') ? $config['log_udp_address'] : 'udp://127.0.0.1:2207';
	}

    // --------------------------------------------------------------------

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param    string $level The error level: 'error', 'debug' or 'info'
     * @param    string $msg The error message
     * @return    bool
     */
    public function write_log($level, $msg)
    {
        $bin_data = $this->encode(serialize ( array (
            'logger_name' => $this->_log_name,
            'logger_level' => $this->_log_att[$level]??$level,
            'logger_message' => $msg
        ) ));
        return $this->sendData($this->_log_udp_address, $bin_data);
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

    public function encode($buffer)
    {
        $total_length = 4 + strlen($buffer);
        return pack('N', $total_length) . $buffer;
    }
}
