<?php
\core\Routing::group('профиль', function () {
    \core\Routing::setForPeer('мой', \controller\control\UserController::class, 'getMy');
    \core\Routing::setForPeer(':user_text', \controller\control\UserController::class, 'get',
        (new \core\Validation())->setValidation('user_text', \core\Validation::REQUIRE));
    \core\Routing::setForPeer('', \controller\control\UserController::class, 'getReply');
});