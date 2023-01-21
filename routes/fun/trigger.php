<?php

use controller\control\TriggerController;
use core\Routing;
use Validation\Validation;

Routing::group('триггер', function () {
    Routing::setForPeer('создать :name', TriggerController::class, 'create', Validation::create()
        ->setValidation('name', Validation::FULL));
    Routing::setForPeer('удалить :name', TriggerController::class, 'delete', Validation::create()
        ->setValidation('name', Validation::FULL));
});

Routing::setForPeer('триггеры', TriggerController::class, 'allTrigger');