<?php

use controller\control\CoreController;
use core\Routing;
use core\Validation;

Routing::group('беседа', function () {
    Routing::setForPeer('инициализация', CoreController::class, 'init');
});

Routing::setForPeer('пинг', CoreController::class, 'ping');
Routing::setCommandForPeer('chat_invite_user', CoreController::class, 'inviteUser');
Routing::setCommandForPeer('chat_invite_user_by_link', CoreController::class, 'inviteUser');
Routing::setCommandForPeer('chat_kick_user', CoreController::class, 'leaveUser');
Routing::setForPeer('помощь', CoreController::class, 'help');

Routing::group('тест', function () {
    Routing::setForPeer(':number', CoreController::class, 'testInt', (new Validation())
        ->setValidation('number', Validation::INTEGER));
    Routing::setForPeer(':text и число :number', CoreController::class, 'testDouble', (new Validation())
        ->setValidation('number', Validation::INTEGER)
        ->setValidation('text', Validation::WORD, Validation::LENGTH('text', 10)));
});