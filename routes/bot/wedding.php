<?php

use controller\control\WeddingController;
use core\Routing;
use Validation\Validation;

Routing::group('мой', function () {
    Routing::setForPeer('брак', WeddingController::class, 'weddingGet');
});
Routing::group('да', function () {
    Routing::setForPeer('', WeddingController::class, 'AnswerKidYes');
});
Routing::group('нет', function () {
    Routing::setForPeer('', WeddingController::class, 'AnswerKidNo');
});
Routing::group('брак', function () {
    Routing::setForPeer(':username', WeddingController::class, 'wedding', Validation::create()
        ->setValidation('username', Validation::FULL));
});
    Routing::setForPeer('-брак', WeddingController::class, 'weddingGet');
Routing::group('усыновить', function () {
    Routing::setForPeer(':username', WeddingController::class, 'SetKids', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('не', function () {
    Routing::setForPeer('согласен', WeddingController::class, 'weddingNo');
});
    Routing::setForPeer('согласен', WeddingController::class, 'weddingYes');
Routing::group('удочерить', function () {
    Routing::setForPeer(':username', WeddingController::class, 'SetKids', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('уйти', function () {
    Routing::group('в', function () {
        Routing::setForPeer('детдом', WeddingController::class, 'FreeChild');
    });
});
Routing::group('мои', function () {
    Routing::setForPeer('дети', WeddingController::class, 'GetKids');
    Routing::setForPeer('родители', WeddingController::class, 'GetParent');
    Routing::setForPeer('предки', WeddingController::class, 'GetParent');
    Routing::setForPeer('родаки', WeddingController::class, 'GetParent');
});
Routing::setForPeer('браки', WeddingController::class, 'weddingAll');