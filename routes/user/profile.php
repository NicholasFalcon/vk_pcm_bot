<?php

use controller\control\UserController;
use core\Routing;
use core\Validation;

Routing::group('профиль', function () {
    Routing::setForPeer('мой', UserController::class, 'getMy');
    Routing::setForPeer(':user_text', UserController::class, 'get',
        (new Validation())->setValidation('user_text', Validation::REQUIRE));
    Routing::setForPeer('', UserController::class, 'getReply');
});
Routing::group('мои', function () {
    Routing::group('беседы', function () {
        Routing::setForPeer('', UserController::class, 'MyPeers');
    });
});

Routing::setForPeer('техподдержка', UserController::class, 'TP');
Routing::group('инфа', function () {
    Routing::setForPeer(':user_text', UserController::class, 'types');
});

Routing::group('ник', function () {
     Routing::setForPeer('', UserController::class, 'SetNick');
});
Routing::group('все', function () {
    Routing::group('ники', function () {
        Routing::setForPeer('', UserController::class, 'GetNick');
    });
});
Routing::group('значок', function () {
    Routing::setForPeer('', UserController::class, 'SetPin');
});
Routing::group('+увед', function () {
    Routing::setForPeer('', UserController::class, 'callMe');
});
Routing::group('-увед', function () {
    Routing::setForPeer('', UserController::class, 'shutUp');
});

Routing::group('неактив', function () {
    Routing::setForPeer('', UserController::class, 'getInactive');
});
Routing::group('поиск', function () {
    Routing::setForPeer(':user_text', UserController::class, 'Search');
});
Routing::group('переведи', function () {
    Routing::setForPeer(':user_text', UserController::class, 'translateText');
});