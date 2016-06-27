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
 * Output Class
 *
 * Responsible for sending final output to the browser.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Output
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/libraries/output.html
 */
class CI_Output {

	/**
	 * Final output string
	 *
	 * @var    string
	 */
	public $final_output;

	/**
	 * List of server headers
	 *
	 * @var    array
	 */
	public $headers = array();

	/**
	 * List of mime types
	 *
	 * @var    array
	 */
	public $mimes = array();

	/**
	 * Mime-type for the current page
	 *
	 * @var    string
	 */
	protected $mime_type = 'text/html';
	/**
	 * php.ini zlib.output_compression flag
	 *
	 * @var    bool
	 */
	protected $_zlib_oc = FALSE;
	/**
	 * CI output compression flag
	 *
	 * @var    bool
	 */
	protected $_compress_output = FALSE;

	/**
	 * Class constructor
	 *
	 * Determines whether zLib output compression will be used.
	 *
	 * @return    void
	 */
	public function __construct()
	{
		$this->_zlib_oc = (bool)ini_get('zlib.output_compression');
		$this->_compress_output = (
			$this->_zlib_oc === FALSE
			&& config_item('compress_output') === TRUE
			&& extension_loaded('zlib')
		);

		// Get mime types for later
		$this->mimes =& get_mimes();

		log_message('info', 'Output Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Output
	 *
	 * Returns the current output string.
	 *
	 * @return	string
	 */
	public function get_output()
	{
		return $this->final_output;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Output
	 *
	 * Sets the output string.
	 *
	 * @param    string $output Output data
	 * @return    CI_Output
	 */
	public function set_output($output)
	{
		$this->final_output = $output;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Append Output
	 *
	 * Appends data onto the output string.
	 *
	 * @param    string $output Data to append
	 * @return    CI_Output
	 */
	public function append_output($output)
	{
		$this->final_output .= $output;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Current Content-Type Header
	 *
	 * @return    string    'text/html', if not already set
	 */
	public function get_content_type()
	{
		for ($i = 0, $c = count($this->headers); $i < $c; $i++)
		{
			if (sscanf($this->headers[$i][0], 'Content-Type: %[^;]', $content_type) === 1) {
				return $content_type;
			}
		}

		return 'text/html';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Set HTTP Status Header
	 *
	 * As of version 1.7.2, this is an alias for common function
	 * set_status_header().
	 *
	 * @param    int $code Status code (default: 200)
	 * @param    string $text Optional message
	 * @return    CI_Output
	 */
	public function set_status_header($code = 200, $text = '')
	{
		set_status_header($code, $text);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Header
	 *
	 * Lets you set a server header which will be sent with the final output.
	 *
	 * Note: If a file is cached, headers will not be sent.
	 * @todo    We need to figure out how to permit headers to be cached.
	 *
	 * @param    string $header Header
	 * @param    bool $replace Whether to replace the old header value, if already set
	 * @return    CI_Output
	 */
	public function set_header($header, $replace = TRUE)
	{
		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if ($this->_zlib_oc && strncasecmp($header, 'content-length', 14) === 0) {
			return $this;
		}

		$this->headers[] = array($header, $replace);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Output
	 *
	 * Processes and sends finalized output data to the browser along
	 * with any server headers and profile data. It also stops benchmark
	 * timers so the page rendering speed and memory usage can be shown.
	 *
	 * Note: All "view" data is automatically put into $this->final_output
	 *     by controller class.
	 *
	 * @uses    CI_Output::$final_output
	 * @param    string $output Output data override
	 * @return    void
	 */
	public function _display($output = '',$connection)
	{
		// Set the output data
		if ($output === '')
		{
			$output =& $this->final_output;
		}

		// Are there any server headers to send?
		if (count($this->headers) > 0)
		{
			foreach ($this->headers as $header)
			{
				@wokerman_header($header[0], $header[1]);
			}
		}
		if ($this->_compress_output === TRUE) {
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
				wokerman_header('Content-Encoding: gzip');
				wokerman_header("Vary: Accept-Encoding");
				$output = gzencode($output." \n",9);
			}
		}
		$connection->send($output);
		log_message('info', 'Final output sent to browser');
		$this->clear();
		return;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Header
	 *
	 * @param    string $header_name
	 * @return    string
	 */
	public function get_header($header)
	{
		// Combine headers already sent with our batched headers
		$headers = array_merge(
		// We only need [x][0] from our multi-dimensional array
			array_map('array_shift', $this->headers),
			headers_list()
		);

		if (empty($headers) OR empty($header)) {
			return NULL;
		}

		for ($i = 0, $c = count($headers); $i < $c; $i++)
		{
			if (strncasecmp($header, $headers[$i], $l = strlen($header)) === 0) {
				return trim(substr($headers[$i], $l + 1));
			}
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Content-Type Header
	 *
	 * @param    string $mime_type Extension of the file we're outputting
	 * @param    string $charset Character set (default: NULL)
	 * @return    CI_Output
	 */
	public function set_content_type($mime_type, $charset = NULL)
	{
		if($this->mime_type==$mime_type){
			return $this;
		}
		if (strpos($mime_type, '/') === FALSE)
		{
			$extension = ltrim($mime_type, '.');

			// Is this extension supported?
			if (isset($this->mimes[$extension])) {
				$mime_type =& $this->mimes[$extension];

				if (is_array($mime_type)) {
					$mime_type = current($mime_type);
				}
			}
		}

		$this->mime_type = $mime_type;

		if (empty($charset))
		{
			$charset = config_item('charset');
		}

		$header = 'Content-Type: ' . $mime_type
			. (empty($charset) ? '' : '; charset=' . $charset);

		$this->headers[] = array($header, TRUE);
		return $this;
	}
	public function clear(){
		unset($this->headers);
		$this->headers = [];
		$this->final_output='';
		$this->mime_type = 'text/html';
	}
}
