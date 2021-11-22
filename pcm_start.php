<?php
include_once 'core/init.php';
$config = file_get_contents('config/api.json');
$router = new \core\Router(json_decode($config)->pcm_bot_test);
if(isset($argv[1]) && $argv[1] != '')
    $router->createAction($argv[1]);
else
    $router->start();