<?php

use controller\control\AdminController;
use controller\control\PeerController;
use controller\control\SettingsController;
use core\Routing;
use core\Validation;

Routing::group('беседа', function () {
    Routing::group('обновить', function () {
        Routing::setForPeer('', PeerController::class, 'update');
    });
    Routing::setForPeer('настройки', PeerController::class, 'getSettings');
    Routing::setForPeer('настройка :setting_id :value', SettingsController::class, 'change', (new Validation())
        ->setValidation('setting_id', Validation::INTEGER, Validation::REQUIRE)
        ->setValidation('value', Validation::REQUIRE));
});

Routing::group('сетка', function () {
    Routing::group('обновить', function () {
        Routing::setForPeer('', PeerController::class, 'updateWeb');
    });
});

Routing::group('созвать', function () {
    Routing::group('всех', function () {
        Routing::setForPeer('', PeerController::class, 'attention');
    });
});

Routing::group('кик', function () {
    Routing::group('неактив', function () {
        Routing::setForPeer(':user_text', PeerController::class, 'KickInactive');
    });
    Routing::setForPeer('собак', AdminController::class, 'KickDeactivated');
});

Routing::group('+автокик', function () {
    Routing::setForPeer('', PeerController::class, 'autoKickOn');
});
Routing::group('-автокик', function () {
    Routing::setForPeer('', PeerController::class, 'autoKickOff');
});
Routing::group('найди', function () {
    Routing::setForPeer(':user_text', PeerController::class, 'search');
});