<?php

use core\Router;

chdir(__DIR__);
include_once 'core/init.php';
$config = file_get_contents('config/api.json');
$router = new Router(json_decode($config)->pcm_bot);
$router->runDaily();