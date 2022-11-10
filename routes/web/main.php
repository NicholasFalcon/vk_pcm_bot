<?php

use controller\control\WebController;
use core\Routing;
use core\Validation;

Routing::group('сетка', function () {
    Routing::setForPeer('список', WebController::class, 'listWeb');
    Routing::setForPeer('привязать', WebController::class, 'peerAddWeb');
    Routing::setForPeer('', WebController::class, 'get');
    Routing::setForPeer('инфо', WebController::class, 'WebInfo');
    Routing::setForPeer('обновить', WebController::class, 'UpdateWeb');
    Routing::setForPeer('отвязать', WebController::class, 'withdraw');
    Routing::setForPeer('настройки :web_id', WebController::class, 'webSettings', (new Validation())
        ->setValidation('web_id', Validation::INTEGER));
    Routing::setForPeer('настройка :web_id :setting_id :value', WebController::class, 'webChangeSetting', (new Validation())
        ->setValidation('web_id', Validation::INTEGER, Validation::REQUIRE)
        ->setValidation('setting_id', Validation::INTEGER, Validation::REQUIRE)
        ->setValidation('value', Validation::REQUIRE));
    Routing::setForPeer('настройка :setting_id :value', WebController::class, 'webChangeSetting', (new Validation())
        ->setValidation('setting_id', Validation::INTEGER, Validation::REQUIRE)
        ->setValidation('value', Validation::REQUIRE));
    Routing::setForPeer('беседы :web_id', WebController::class, 'peerList', (new Validation())
        ->setValidation('web_id', Validation::INTEGER));
    Routing::group('топ', function () {
        Routing::setForPeer('дня :web_id', WebController::class, 'day', (new Validation())
            ->setValidation('web_id', Validation::INTEGER));
        Routing::setForPeer('недели :web_id', WebController::class, 'week',(new Validation())
            ->setValidation('web_id', Validation::INTEGER));
        Routing::setForPeer(':web_id', WebController::class, 'all', (new Validation())
            ->setValidation('web_id', Validation::INTEGER));
        Routing::group('бесед', function () {
            Routing::setForPeer('дня :web_id', WebController::class, 'peerDay', (new Validation())
                ->setValidation('web_id', Validation::INTEGER));
            Routing::setForPeer('недели :web_id', WebController::class, 'peerWeek', (new Validation())
                ->setValidation('web_id', Validation::INTEGER));
            Routing::setForPeer(':web_id', WebController::class, 'peerAll', (new Validation())
                ->setValidation('web_id', Validation::INTEGER));
        });
    });
    Routing::setCommandForPeer('web_connect :web_id', WebController::class, 'connect');
});