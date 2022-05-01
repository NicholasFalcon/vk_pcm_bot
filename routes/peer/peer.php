<?php

use controller\control\PeerController;
use core\Routing;

Routing::group('беседа', function () {
    Routing::group('обновить', function () {
        Routing::setForPeer('', PeerController::class, 'update');
    });
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
});

Routing::group('+автокик', function () {
    Routing::setForPeer('', PeerController::class, 'autoKickOn');
});
Routing::group('-автокик', function () {
    Routing::setForPeer('', PeerController::class, 'autoKickOff');
});
Routing::group('найди', function () {
    Routing::setForPeer(':user_text', PeerController::class, 'autoKickOff');
});