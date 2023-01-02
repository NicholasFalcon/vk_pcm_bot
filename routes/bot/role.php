<?php

use core\Routing;
use controller\user\RoleController;
use Validation\Validation;

//Управление ролями
Routing::setCommandForUser('create_role', RoleController::class, 'create');
Routing::setCommandForUser('edit_role :role_id', RoleController::class, 'edit');
Routing::setCommandForUser('role_delete', RoleController::class, 'delete');
Routing::setCommandForUser('role_title :role_id', RoleController::class, 'title');

//Доступы роли к командам
Routing::setCommandForUser('role_access_trigger', RoleController::class, 'accessRoleTrigger');
Routing::setCommandForUser('role_access_kick', RoleController::class, 'accessRoleKick');
Routing::setCommandForUser('role_access_ban', RoleController::class, 'accessRoleBan');
Routing::setCommandForUser('role_access_pred', RoleController::class, 'accessRolePred');
Routing::setCommandForUser('role_access_message_peer', RoleController::class, 'accessRoleMessagePeer');
Routing::setCommandForUser('role_access_role_editor', RoleController::class, 'accessRoleEditorRole');
Routing::setCommandForUser('role_access_mute', RoleController::class, 'accessRoleMute');
Routing::setCommandForUser('role_access_immune', RoleController::class, 'accessRoleImmune');
Routing::setCommandForUser('role_access_end', RoleController::class, 'accessRoleEnd');