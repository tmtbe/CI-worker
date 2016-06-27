<?php
namespace Server;

use Workerman\Autoloader;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

define('APPPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('BASEPATH', __DIR__ . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
// 自动加载类
require_once APPPATH . '../../Workerman/Autoloader.php';
Autoloader::setRootPath(APPPATH);
require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'core/Controller.php';
require_once BASEPATH . 'core/Model.php';
require_once APPPATH . 'core/Base_Controller.php';

class HttpServer
{

    /**
     * Worker instance.
     * @var Worker
     */
    protected $_worker = null;
    protected $_cache = [];
    protected $_client;
    /**
     * @var \CI_Loader
     */
    public $load;
    /**
     * @var \CI_Input
     */
    public $input;
    /**
     * @var \CI_Output
     */
    public $output;
    /**
     * @var \CI_Config
     */
    public $config;
    /**
     * Reference to the CI singleton
     *
     * @var    object
     */
    private static $instance;

    /**
     * Construct.
     * @param string $ip
     * @param int $port
     */
    public function __construct()
    {
        $worker = new Worker('http://0.0.0.0:8081');
        $worker->count = 4;
        $worker->name = 'HttpServer';
        $worker->onWorkerStart = array($this, 'onWorkerStart');
        $worker->onWorkerStop = array($this, 'onWorkerStop');
        $worker->onMessage = array($this, 'onMessage');
        $this->_worker = $worker;
        self::$instance =& $this;
    }

    public function onWorkerStart()
    {
        $this->config =& load_class('Config', 'core');
        $this->load =& load_class('Loader', 'core');
        $this->input =& load_class('Input', 'core');
        $this->output =& load_class('Output', 'core');
        $this->load->initialize();
        date_default_timezone_set('Asia/Shanghai');
    }

    public function onWorkerStop()
    {

    }

    public function onMessage(TcpConnection $connection, $data)
    {
        $this->input->clear();
        list($em, $class, $method) = explode('/', $_SERVER['PHP_URL_PATH'], 3);
        $class = ucfirst($class);
        $e404 = $this->loadController($class, $method);
        if ($e404) {
            $this->output->set_content_type('application/json; charset=UTF-8');
            $data = ['code' => 404, 'msg' => '页面不存在'];
            $this->output->final_output = json_encode($data, JSON_UNESCAPED_UNICODE);
            $this->output->_display('', $connection);
        } else {
            try {
                $CL = $this->getClassFromCache($class);
                call_user_func_array(array(&$CL, $method), []);
            } catch (\DoerError $e) {
                $this->output->set_content_type('application/json; charset=UTF-8');
                $data = ['code' => $e->getCode(), 'msg' => $e->getMessage(), 'errorData' => $e->others];
                $this->output->final_output = json_encode($data, JSON_UNESCAPED_UNICODE);
            } finally {
                $this->output->_display('', $connection);
            }
        }
    }

    public function loadController($class, $method)
    {
        if(empty($class)||empty($method)){
            return TRUE;
        }
        $e404 = FALSE;
        if (!array_key_exists($class, $this->_cache)) {
            if (empty($class) OR !file_exists(APPPATH . 'controllers/' . $class . '.php')) {
                $e404 = TRUE;
            } else {
                require_once(APPPATH . 'controllers/' . $class . '.php');
                if (!class_exists($class, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method)) {
                    $e404 = TRUE;
                } elseif (!in_array(strtolower($method), array_map('strtolower', get_class_methods($class)), TRUE)) {
                    $e404 = TRUE;
                }
            }
            if (!$e404) {
                $this->setCache($class);
            }
        }
        return $e404;
    }

    public function setCache($var, $value = null)
    {
        $this->_cache[$var] = $value;
    }

    public function getClassFromCache($class)
    {
        $temp = $this->_cache[$class];
        if ($temp == null) {
            $temp = $this->_cache[$class] = new $class;
        }
        $temp->__CI_construct();
        return $temp;
    }

    /**
     * Get the CI singleton
     *
     * @static
     * @return    object
     */
    public static function &get_instance()
    {
        return self::$instance;
    }
}