<?php

use controller\control\AdminController;
use controller\control\PeerController;
use controller\control\SettingsController;
use core\Routing;
use Validation\Validation;
use Validation\Validators\IntValidator;
use Validation\Validators\RequireValidator;

Routing::group('беседа', function () {
    Routing::group('обновить', function () {
        Routing::setForPeer('', PeerController::class, 'update');
    });
    Routing::setForPeer('настройки', PeerController::class, 'getSettings');
    Routing::setForPeer('настройка :setting_id :value', SettingsController::class, 'change', Validation::create()
        ->setValidation('setting_id',
            IntValidator::create(),
            RequireValidator::create())
        ->setValidation('value',
            RequireValidator::create()));
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
        Routing::setForPeer(':days', PeerController::class, 'KickInactive', Validation::create()
            ->setValidation('days', IntValidator::create()));
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
    Routing::setForPeer(':name', PeerController::class, 'search', Validation::create()
        ->setValidation('name', Validation::FULL));
});