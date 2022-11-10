<?php

use core\Routing;
use controller\user\WebController;

Routing::setCommandForUser('create_web', WebController::class, 'create');
Routing::setCommandForUser('edit_web :web_id', WebController::class, 'edit');
Routing::setCommandForUser('web_delete :web_id', WebController::class, 'delete');
Routing::setCommandForUser('web_title :web_id', WebController::class, 'title');
Routing::setCommandForUser('web_add_admin :web_id', WebController::class, 'addAdmin');
Routing::setCommandForUser('web_del_admin :web_id', WebController::class, 'delAdmin');