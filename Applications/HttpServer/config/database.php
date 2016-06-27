<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$active_group = 'default';
$active_record = TRUE;

$db['default'] = array(
    'hostname' => '112.124.234.121',
    'username' => 'test',
    'password' => 'test',
    'database' => 'test',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => TRUE,
    'db_debug' => TRUE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'autoinit' => TRUE,
    'stricton' => FALSE,
    'compress' => TRUE
);


/* Location: ./application/config/database.php */