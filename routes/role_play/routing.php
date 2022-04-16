<?php

use controller\fun\RolePlayController;
use core\Routing;
use core\Validation;

Routing::setForPeer(':user_text', RolePlayController::class, 'shoot');
