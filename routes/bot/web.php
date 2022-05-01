<?php

use core\Routing;
use controller\user\WebController;

Routing::setCommandForUser('create_web', WebController::class, 'create');
Routing::setCommandForUser('edit_web', WebController::class, 'edit');
Routing::setCommandForUser('web_delete', WebController::class, 'delete');
Routing::setCommandForUser('web_title', WebController::class, 'title');
Routing::setCommandForUser('web_add_admin', WebController::class, 'addAdmin');
Routing::setCommandForUser('web_del_admin', WebController::class, 'delAdmin');
Routing::setCommandForUser('web_del_admin', WebController::class, 'delAdmin');