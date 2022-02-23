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