<?php

use controller\control\WebController;
use core\Routing;
use Validation\Validation;
use Validation\Validators\IntValidator;
use Validation\Validators\RequireValidator;

Routing::group('сетка', function () {
    Routing::setForPeer('список', WebController::class, 'listWeb');
    Routing::setForPeer('привязать', WebController::class, 'peerAddWeb');
    Routing::setForPeer('', WebController::class, 'get');
    Routing::setForPeer('инфо', WebController::class, 'WebInfo');
    Routing::setForPeer('обновить', WebController::class, 'UpdateWeb');
    Routing::setForPeer('отвязать', WebController::class, 'withdraw');
    Routing::setForPeer('настройки :web_id', WebController::class, 'webSettings', Validation::create()
        ->setValidation('web_id', IntValidator::create()));
    Routing::setForPeer('настройка :web_id :setting_id :value', WebController::class, 'webChangeSetting', Validation::create()
        ->setValidation('web_id', IntValidator::create(), RequireValidator::create())
        ->setValidation('setting_id', IntValidator::create(), RequireValidator::create())
        ->setValidation('value', RequireValidator::create()));
    Routing::setForPeer('настройка :setting_id :value', WebController::class, 'webChangeSetting', Validation::create()
        ->setValidation('setting_id', IntValidator::create(), RequireValidator::create())
        ->setValidation('value', RequireValidator::create()));
    Routing::setForPeer('беседы :web_id', WebController::class, 'peerList', Validation::create()
        ->setValidation('web_id', IntValidator::create()));
    Routing::group('топ', function () {
        Routing::setForPeer('дня :web_id', WebController::class, 'day', Validation::create()
            ->setValidation('web_id', IntValidator::create()));
        Routing::setForPeer('недели :web_id', WebController::class, 'week', Validation::create()
            ->setValidation('web_id', IntValidator::create()));
        Routing::setForPeer(':web_id', WebController::class, 'all', Validation::create()
            ->setValidation('web_id', IntValidator::create()));
        Routing::group('бесед', function () {
            Routing::setForPeer('дня :web_id', WebController::class, 'peerDay', Validation::create()
                ->setValidation('web_id', IntValidator::create()));
            Routing::setForPeer('недели :web_id', WebController::class, 'peerWeek', Validation::create()
                ->setValidation('web_id', IntValidator::create()));
            Routing::setForPeer(':web_id', WebController::class, 'peerAll', Validation::create()
                ->setValidation('web_id', IntValidator::create()));
        });
    });
    Routing::setCommandForPeer('web_connect :web_id', WebController::class, 'connect');
});