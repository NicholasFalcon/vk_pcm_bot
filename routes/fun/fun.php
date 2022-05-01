<?php

use core\Routing;
use controller\fun\FunController;

Routing::setForPeer('беседы', FunController::class, 'Peers');
Routing::setForPeer('погода', FunController::class, 'Weather');
Routing::setForPeer('шипперим', FunController::class, 'ShipShip');
Routing::setForPeer('шиперим', FunController::class, 'ShipShip');
Routing::setForPeer('биржа', FunController::class, 'stockMarket');
Routing::setForPeer('новости', FunController::class, 'News');

Routing::group('написать', function () {
    Routing::setForPeer(':user_text', FunController::class, 'sendMes');
});