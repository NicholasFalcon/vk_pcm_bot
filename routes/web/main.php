<?php

use controller\control\WebController;
use core\Routing;

Routing::group('сетка', function () {
    Routing::setForPeer('список', WebController::class, 'listWeb');
    Routing::setForPeer('привязать', WebController::class, 'peerAddWeb');
    Routing::setForPeer('', WebController::class, 'get');
    Routing::setForPeer('инфо :user_text', WebController::class, 'WebInfo');
    Routing::setForPeer('обновить', WebController::class, 'UpdateWeb');
    Routing::setForPeer('отвязать', WebController::class, 'withdraw');
    Routing::setForPeer('настройки :user_text', WebController::class, 'webSettings');
    Routing::setForPeer('настройка :user_text', WebController::class, 'webChangeSetting');
    Routing::setForPeer('беседы :user_text', WebController::class, 'peerList');
    Routing::group('топ', function () {
        Routing::setForPeer('дня :user_text', WebController::class, 'day');
        Routing::setForPeer('недели :user_text', WebController::class, 'week');
        Routing::setForPeer(':user_text', WebController::class, 'all');
        Routing::group('бесед', function () {
            Routing::setForPeer('дня :user_text', WebController::class, 'peerDay');
            Routing::setForPeer('недели :user_text', WebController::class, 'peerWeek');
            Routing::setForPeer(':user_text', WebController::class, 'peerAll');
        });
    });
    Routing::setCommandForPeer('web_connect :user_text', WebController::class, 'connect');
});