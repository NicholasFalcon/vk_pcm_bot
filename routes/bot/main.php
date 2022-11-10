<?php

use controller\control\UserController;
use core\Routing;
use controller\user\MainController;

Routing::group('бот', function () {
    Routing::setForPeer('увед', UserController::class, 'callMe');
    Routing::setForPeer('-увед', UserController::class, 'shutUp');
});

Routing::setForUser('начать', MainController::class, 'start');
Routing::setCommandForUser('start', MainController::class, 'start');
Routing::setCommandForUser('menu1', MainController::class, 'menu1');
Routing::setCommandForUser('menu2', MainController::class, 'menu2');
Routing::setCommandForUser('menu3', MainController::class, 'menu3');
Routing::setCommandForUser('menu4', MainController::class, 'menu4');