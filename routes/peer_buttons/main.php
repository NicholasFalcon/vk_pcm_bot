<?php

use controller\control\AcceptController;
use controller\control\DeclineController;
use core\Routing;

Routing::setForPeer('подтверждаю :user_text', AcceptController::class, 'index');
Routing::setForPeer('подтверждения', AcceptController::class, 'list');
Routing::setForPeer('отказываюсь :user_text', DeclineController::class, 'index');