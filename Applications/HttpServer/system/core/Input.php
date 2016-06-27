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
 * Input Class
 *
 * Pre-processes global input data for security
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Input
 * @author        EllisLab Dev Team
 * @link        https://codeigniter.com/user_guide/libraries/input.html
 */
class CI_Input {

	/**
	 * Enable XSS flag
	 *
	 * Determines whether the XSS filter is always active when
	 * GET, POST or COOKIE data is encountered.
	 * Set automatically based on config setting.
	 *
	 * @var    bool
	 */
	protected $_enable_xss = FALSE;

	/**
	 * Enable CSRF flag
	 *
	 * Enables a CSRF cookie token to be set.
	 * Set automatically based on config setting.
	 *
	 * @var    bool
	 */
	protected $_enable_csrf = FALSE;

	/**
	 * List of all HTTP request headers
	 *
	 * @var array
	 */
	protected $headers = array();
	protected $security;
    /**
     * IP address of the current user
     *
     * @var    string
     */
    protected $ip_address = FALSE;
	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Determines whether to globally enable the XSS processing
	 * and whether to allow the $_GET array.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->_enable_xss		= (config_item('global_xss_filtering') === TRUE);
		$this->_enable_csrf		= (config_item('csrf_protection') === TRUE);

		$this->security =& load_class('Security', 'core');

		// CSRF Protection check
		if ($this->_enable_csrf === TRUE) {
			$this->security->csrf_verify();
		}

		log_message('info', 'Input Class Initialized');
	}
	// --------------------------------------------------------------------

	/**
	 * Fetch an item from POST data with fallback to GET
	 *
	 * @param    string $index Index for item to be fetched from $_POST or $_GET
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	public function post_get($index, $xss_clean = NULL)
	{
		return isset($_POST[$index])
			? $this->post($index, $xss_clean)
			: $this->get($index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the POST array
	 *
	 * @param    mixed $index Index for item to be fetched from $_POST
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	public function post($index = NULL, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_POST, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch from array
	 *
	 * Internal method used to retrieve values from global arrays.
	 *
	 * @param    array &$array $_GET, $_POST, $_COOKIE, $_SERVER, etc.
	 * @param    mixed $index Index for item to be fetched from $array
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	protected function _fetch_from_array(&$array, $index = NULL, $xss_clean = NULL)
	{
		is_bool($xss_clean) OR $xss_clean = $this->_enable_xss;

		// If $index is NULL, it means that the whole $array is requested
		isset($index) OR $index = array_keys($array);

		// allow fetching multiple keys at once
		if (is_array($index))
		{
			$output = array();
			foreach ($index as $key) {
				$output[$key] = $this->_fetch_from_array($array, $key, $xss_clean);
			}

			return $output;
		}

		if (isset($array[$index])) {
			$value = $array[$index];
		} elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) // Does the index contain array notation
		{
			$value = $array;
			for ($i = 0; $i < $count; $i++) {
				$key = trim($matches[0][$i], '[]');
				if ($key === '') // Empty notation will return the value as array
				{
					break;
				}

				if (isset($value[$key])) {
					$value = $value[$key];
				} else {
					return NULL;
				}
			}
		}
		else
		{
			return NULL;
		}

		return ($xss_clean === TRUE)
			? $this->security->xss_clean($value)
			: $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the GET array
	 *
	 * @param    mixed $index Index for item to be fetched from $_GET
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	public function get($index = NULL, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_GET, $index, $xss_clean);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch an item from GET data with fallback to POST
	 *
	 * @param    string $index Index for item to be fetched from $_GET or $_POST
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	public function get_post($index, $xss_clean = NULL)
	{
		return isset($_GET[$index])
			? $this->get($index, $xss_clean)
			: $this->post($index, $xss_clean);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @param    mixed $index Index for item to be fetched from $_COOKIE
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	public function cookie($index = NULL, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Set cookie
	 *
	 * Accepts an arbitrary number of parameters (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param    string|mixed[] $name Cookie name or an array containing parameters
	 * @param    string $value Cookie value
	 * @param    int $expire Cookie expiration time in seconds
	 * @param    string $domain Cookie domain (e.g.: '.yourdomain.com')
	 * @param    string $path Cookie path (default: '/')
	 * @param    string $prefix Cookie name prefix
	 * @param    bool $secure Whether to only transfer cookies via SSL
	 * @param    bool $httponly Whether to only makes the cookie accessible via HTTP (no javascript)
	 * @return    void
	 */
	public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE)
	{
		if (is_array($name))
		{
			// always leave 'name' in last place, as the loop will break otherwise, due to $$item
			foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
			{
				if (isset($name[$item]))
				{
					$$item = $name[$item];
				}
			}
		}

		if ($prefix === '' && config_item('cookie_prefix') !== '')
		{
			$prefix = config_item('cookie_prefix');
		}

		if ($domain == '' && config_item('cookie_domain') != '')
		{
			$domain = config_item('cookie_domain');
		}

		if ($path === '/' && config_item('cookie_path') !== '/')
		{
			$path = config_item('cookie_path');
		}

		if ($secure === FALSE && config_item('cookie_secure') === TRUE)
		{
			$secure = config_item('cookie_secure');
		}

		if ($httponly === FALSE && config_item('cookie_httponly') !== FALSE) {
			$httponly = config_item('cookie_httponly');
		}

		if ( ! is_numeric($expire))
		{
			$expire = time() - 86500;
		}
		else
		{
			$expire = ($expire > 0) ? time() + $expire : 0;
		}

		workerman_setcookie($prefix . $name, $value, $expire, $path, $domain, $secure, $httponly);
	}
	// --------------------------------------------------------------------

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param    mixed $index Index for item to be fetched from $_SERVER
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    mixed
	 */
	public function server($index, $xss_clean = NULL)
	{
		return $this->_fetch_from_array($_SERVER, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch User Agent string
	 *
	 * @return    string|null    User Agent string or NULL if it doesn't exist
	 */
	public function user_agent($xss_clean = NULL)
	{
		return $this->_fetch_from_array($_SERVER, 'HTTP_USER_AGENT', $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param    string $index Header name
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    string|null    The requested header on success or NULL on failure
	 */
	public function get_request_header($index, $xss_clean = FALSE)
	{
	    $this->request_headers();
		if (!isset($this->headers[$index]))
		{
			return NULL;
		}

		return ($xss_clean === TRUE)
			? $this->security->xss_clean($this->headers[$index])
			: $this->headers[$index];
	}

	// --------------------------------------------------------------------

	/**
	 * Request Headers
	 *
	 * @param    bool $xss_clean Whether to apply XSS filtering
	 * @return    array
	 */
	public function request_headers($xss_clean = FALSE)
	{
		// If header is already defined, return it immediately
		if (!empty($this->headers))
		{
			return $this->headers;
		}

		$this->headers = workerman_request_headers();

        /*$this->headers['Content-Type'] = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

        foreach ($_SERVER as $key => $val)
        {
            if (sscanf($key, 'HTTP_%s', $header) === 1)
            {
                // take SOME_HEADER and turn it into Some-Header
                $header = str_replace('_', ' ', strtolower($header));
                $header = str_replace(' ', '-', ucwords($header));

                $this->headers[$header] = $this->_fetch_from_array($_SERVER, $key, $xss_clean);
            }
        }*/

		return $this->headers;
	}

	// --------------------------------------------------------------------

	/**
	 * Is AJAX request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
	 *
	 * @return    bool
	 */
	public function is_ajax_request()
	{
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Request Method
	 *
	 * Return the request method
	 *
	 * @param    bool $upper Whether to return in upper or lower case
	 *                (default: FALSE)
	 * @return    string
	 */
	public function method($upper = FALSE)
	{
		return ($upper)
			? strtoupper($this->server('REQUEST_METHOD'))
			: strtolower($this->server('REQUEST_METHOD'));
	}

    /**
     * Fetch the IP Address
     *
     * Determines and validates the visitor's IP address.
     *
     * @return    string    IP address
     */
    public function ip_address()
    {
        $this->ip_address = $_SERVER['REMOTE_ADDR'];
        return $this->ip_address;
    }

    // ------------------------------------------------------------------------

    /**
     * Magic __get()
     *
     * Allows read access to protected properties
     *
     * @param    string $name
     * @return    mixed
     */
    public function __get($name)
    {
        if ($name === 'ip_address') {
            return $this->ip_address;
        }
    }

    public function clear(){
        unset($this->headers);
        $this->headers = [];
    }
}
