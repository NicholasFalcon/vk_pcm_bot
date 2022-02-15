<?php

use core\App;

error_reporting(-1);

while (!file_exists('vendor/autoload.php'))
{
    sleep(10);
}

include_once 'vendor/autoload.php';
App::init();