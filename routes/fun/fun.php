<?php

use core\Routing;
use controller\fun\FunController;
use Validation\Validation;
use Validation\Validators\IntValidator;
use Validation\Validators\RequireValidator;

Routing::setForPeer('беседы', FunController::class, 'Peers');
Routing::setForPeer('погода', FunController::class, 'Weather');
Routing::setForPeer('шипперим', FunController::class, 'ShipShip');
Routing::setForPeer('шиперим', FunController::class, 'ShipShip');
Routing::setForPeer('биржа', FunController::class, 'stockMarket');
Routing::setForPeer('новости', FunController::class, 'News');

Routing::group('написать', function () {
    Routing::setForPeer(':peer_id :text', FunController::class, 'sendMes', Validation::create()
        ->setValidation('peer_id', IntValidator::create(), RequireValidator::create())
        ->setValidation('text', Validation::FULL, RequireValidator::create()));
});