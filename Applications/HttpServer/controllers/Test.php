<?php
//echo header("Access-Control-Allow-Origin:*");

/**
 * Created by tmtbe on 16-6-22.
 * Class Test
 */

if (!defined('BASEPATH')) {
    exit('Access Denied');
}


class Test extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();
      
    }
   
    public function test()
    {
        $this->output_json(1,1);
    }
}