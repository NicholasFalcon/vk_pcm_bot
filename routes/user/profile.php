<?php

use controller\control\UserController;
use core\Routing;
use Validation\Validation;
use Validation\Validators\RequireValidator;

Routing::group('профиль', function () {
    Routing::setForPeer('мой', UserController::class, 'getMy');
    Routing::setForPeer(':username', UserController::class, 'get', Validation::create()
        ->setValidation('username', RequireValidator::create(), Validation::FULL));
    Routing::setForPeer('', UserController::class, 'getReply');
});
Routing::group('мои', function () {
    Routing::group('беседы', function () {
        Routing::setForPeer('', UserController::class, 'MyPeers');
    });
});

Routing::setForPeer('техподдержка', UserController::class, 'TP');
Routing::group('инфа', function () {
    Routing::setForPeer(':text', UserController::class, 'types', Validation::create()
        ->setValidation('text', Validation::FULL));
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
    Routing::setForPeer(':doc_name', UserController::class, 'Search', Validation::create()
        ->setValidation('doc_name', Validation::FULL));
});
Routing::group('переведи', function () {
    Routing::setForPeer('', UserController::class, 'translateText');
});