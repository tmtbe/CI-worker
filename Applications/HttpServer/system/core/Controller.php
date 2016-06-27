<?php
/**
 * Created by PhpStorm.
 * User: tmtbe
 * Date: 16-6-23
 * Time: 上午9:39
 * @property CI_Loader $load
 * @property CI_DB_driver $db
 * @property CI_Log $log
 * @property CI_Output $output
 * @property CI_Input $input
 * @property CI_Config $config
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 */
class CI_Controller{
    public $input;
    public $output;
    public $load;
    /**
     * load全写在这
     * CI_Controller constructor.
     */
    public function __construct()
    {
        $this->input = get_instance()->input;
        $this->output = get_instance()->output;
        $this->load = get_instance()->load;
    }

    /**
     * 每次访问方法都会执行这里
     */
    public function __CI_construct(){

    }
    public function __get($name)
    {
        return get_instance()->$name;
    }
}
