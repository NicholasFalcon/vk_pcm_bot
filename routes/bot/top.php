<?php

use controller\control\TopController;
use core\Routing;

Routing::group('топ', function () {
    Routing::setForPeer('', TopController::class, 'all');
    Routing::setForPeer('дня', TopController::class, 'day');
    Routing::setForPeer('недели', TopController::class, 'week');
});