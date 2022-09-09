<?php

use controller\control\TriggerController;
use core\Routing;

Routing::group('триггер', function () {
    Routing::setForPeer('создать :user_text', TriggerController::class, 'create');
    Routing::setForPeer('удалить :user_text', TriggerController::class, 'delete');
    Routing::setForPeer('список', TriggerController::class, 'allTrigger');
});