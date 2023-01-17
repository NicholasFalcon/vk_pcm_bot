<?php

use model\Peer;
use Swoole\Runtime;

include_once 'core/init.php';
try {
    Runtime::enableCoroutine();
} catch (\Exception $e)
{
    echo $e->getMessage().PHP_EOL;
    return;
}
\Swoole\Coroutine\run(function () {
    $config = file_get_contents('config/api.json');
    $router = new \core\Router(json_decode($config)->pcm_bot_test);
    if (isset($argv[1]) && $argv[1] != '')
        $router->createAction($argv[1]);
    else
        $router->start();
});