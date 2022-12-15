<?php

use controller\control\CoreController;
use core\Routing;
use Validation\Properties\LengthProperty;
use Validation\Validation;
use Validation\Validators\IntValidator;
use Validation\Validators\LengthValidator;
use Validation\Validators\NotRequireValidator;
use Validation\Validators\WordValidator;

Routing::group('беседа', function () {
    Routing::setForPeer('инициализация', CoreController::class, 'init');
});

Routing::setForPeer('пинг', CoreController::class, 'ping');
Routing::setCommandForPeer('chat_invite_user', CoreController::class, 'inviteUser');
Routing::setCommandForPeer('chat_invite_user_by_link', CoreController::class, 'inviteUser');
Routing::setCommandForPeer('chat_kick_user', CoreController::class, 'leaveUser');
Routing::setForPeer('помощь', CoreController::class, 'help');

Routing::group('тест', function () {
    Routing::setForPeer(':number', CoreController::class, 'testInt', Validation::create()
        ->setValidation('number', IntValidator::create()));
    Routing::setForPeer(':text и число :number', CoreController::class, 'testDouble', Validation::create()
        ->setValidation('number', NotRequireValidator::create()->children(IntValidator::create()))
        ->setValidation('text', WordValidator::create(), LengthValidator::create(LengthProperty::create()
            ->set(LengthProperty::MAX, 10))));
});