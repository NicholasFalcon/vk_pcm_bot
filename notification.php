<?php

use core\App;
use core\Router;

chdir(__DIR__);
include_once 'core/init.php';
App::init();
$config = file_get_contents('config/api.json');
$router = new Router(json_decode($config)->pcm_bot_test);
$router->sendNotification();
$router->runCallback();