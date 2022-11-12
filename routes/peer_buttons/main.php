<?php

use controller\control\AcceptController;
use controller\control\DeclineController;
use core\Routing;
use Validation\Validation;
use Validation\Validators\IntValidator;

Routing::setForPeer('подтверждаю :id', AcceptController::class, 'index', Validation::create()
    ->setValidation('id', IntValidator::create()));
Routing::setForPeer('подтверждения', AcceptController::class, 'list');
Routing::setForPeer('отказываюсь :id', DeclineController::class, 'index', Validation::create()
    ->setValidation('id', IntValidator::create()));