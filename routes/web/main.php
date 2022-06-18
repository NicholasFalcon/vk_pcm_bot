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
    Routing::setForPeer('беседы', WebController::class, 'peerList');
    Routing::group('топ', function () {
        Routing::setForPeer('', WebController::class, 'all');
        Routing::setForPeer('дня', WebController::class, 'day');
        Routing::setForPeer('недели', WebController::class, 'week');
        Routing::group('топ', function () {
            Routing::setForPeer('', WebController::class, 'peerAll');
            Routing::setForPeer('дня', WebController::class, 'peerDay');
            Routing::setForPeer('недели', WebController::class, 'peerWeek');
        });
    });
    Routing::setCommandForPeer('web_connect :user_text', WebController::class, 'connect');
});