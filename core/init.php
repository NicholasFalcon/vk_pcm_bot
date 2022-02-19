<?php

use core\App;

error_reporting(-1);

while (!file_exists('vendor/autoload.php'))
{
    sleep(10);
}

include_once 'vendor/autoload.php';
App::init();
try {
    \core\Routing::installRoute('routes');
}
catch (Exception $e)
{
    echo 'Exception: '.$e->getMessage();
}
