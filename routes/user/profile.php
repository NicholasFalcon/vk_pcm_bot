<?php
\core\Routing::group('профиль', function () {
    \core\Routing::set('мой', \controller\control\UserController::class, 'getMy');
    \core\Routing::set(':user_text', \controller\control\UserController::class, 'get',
        (new \core\Validation())->setValidation('user_text', \core\Validation::REQUIRE));
    \core\Routing::set('', \controller\control\UserController::class, 'getReply');
});