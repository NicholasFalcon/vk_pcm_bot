<?php

use controller\control\AdminController;
use core\Routing;

Routing::group('кик', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'kick');

});