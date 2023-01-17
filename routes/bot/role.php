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
Routing::setCommandForUser('role_access_trigger :role_id', RoleController::class, 'accessRoleTrigger');
Routing::setCommandForUser('role_access_kick :role_id :value', RoleController::class, 'accessRoleKick');
Routing::setCommandForUser('role_access_ban :role_id :value', RoleController::class, 'accessRoleBan');
Routing::setCommandForUser('role_access_pred :role_id :value', RoleController::class, 'accessRolePred');
Routing::setCommandForUser('role_access_message_peer :role_id :value', RoleController::class, 'accessRoleMessagePeer');
Routing::setCommandForUser('role_access_role_editor :role_id :value', RoleController::class, 'accessRoleEditorRole');
Routing::setCommandForUser('role_access_mute :role_id :value', RoleController::class, 'accessRoleMute');
Routing::setCommandForUser('role_access_immune :role_id :value', RoleController::class, 'accessRoleImmune');
Routing::setCommandForUser('role_access_end :role_id :value', RoleController::class, 'accessRoleEnd');