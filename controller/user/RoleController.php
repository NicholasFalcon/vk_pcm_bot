<?php

namespace controller\user;

use core\Controller;
use core\Response;
use model\Role;

class RoleController extends Controller
{
    public function createAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $count = Role::getCountByOwnerId($this->user->id);
        if($count >= 5)
        {
            $response->message = 'Вы достигли ограничения на создание ролей';
            return $response;
        }
        $response->message = 'Новая роль создана, введите следующим сообщением название новой роли';
        $role = new Role();
        $role->owner_id = $this->user->id;
        $role->title = 'Новая роль';
        $role->save();
        $id = Role::getLastId("`owner_id` = {$this->user->id}");
        $this->user->user_action = "editRoleTitle $id";
        $this->user->save();
        return $response;
    }

    public function editAction($role_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $role_id = intval($role_id);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Ошибка системы, скорее всего данная роль уже удалена';
            return $response;
        }
        $response->message = 'Доступы роли:'.PHP_EOL;
        $accesses = $role->getAccesses();
        foreach ($accesses as $access)
        {
            $access_config = json_decode(file_get_contents("config/access.json"), true);
            $response->message .= "- {$access_config[$access['access_id']]['title']}".PHP_EOL;
        }
        $response->message .= 'Выберите, что сделать с ролью:';
        $response->setButtonRow(['Изменить доступ', "role_access_trigger $role->id", Response::PRIMARY]);
        $response->setButtonRow(['Изменить название', "role_title $role->id", Response::PRIMARY]);
        $response->setButtonRow(['Удалить', "role_delete $role->id", Response::NEGATIVE]);
        return $response;
    }

    public function deleteAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $role_id = intval($user_text);
        $role = Role::findById($role_id);
        if($role === false)
        {
            return $response;
        }
        $role->clearRole();
        $role->delete();
        $response->message = 'Роль удалена';
        return $response;
    }

    public function titleAction($role_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $role = Role::findById(intval($role_id));
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        $response->message = 'Введите следующим сообщением название роли:';
        $this->user->user_action = "editRoleTitle $role->id";
        $this->user->save();
        return $response;
    }

    public function accessRoleTriggerAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $role_id = intval($user_text);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if($role->haveAccess(Role::TRIGGER_EDITOR_ACCESS))
        {
            $response->message = "Убрать доступ к управлению триггерами?";
            $response->setButtonRow(['Убрать', "role_access_kick $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_kick $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к управлению триггерами?";
            $response->setButtonRow(['Предоставить', "role_access_kick $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_kick $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleKickAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::TRIGGER_EDITOR_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::TRIGGER_EDITOR_ACCESS);
            }
        }
        if($role->haveAccess(Role::KICK_ACCESS))
        {
            $response->message = "Убрать доступ к удалению пользователей из беседы?";
            $response->setButtonRow(['Убрать', "role_access_ban $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_ban $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к удалению пользователей из беседы?";
            $response->setButtonRow(['Предоставить', "role_access_ban $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_ban $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleBanAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::KICK_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::KICK_ACCESS);
            }
        }
        if($role->haveAccess(Role::BAN_ACCESS))
        {
            $response->message = "Убрать доступ к блокированию пользователей в беседе?";
            $response->setButtonRow(['Убрать', "role_access_pred $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_pred $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к блокированию пользователей в беседе?";
            $response->setButtonRow(['Предоставить', "role_access_pred $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_pred $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRolePredAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::BAN_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::BAN_ACCESS);
            }
        }
        if($role->haveAccess(Role::PRED_ACCESS))
        {
            $response->message = "Убрать доступ к выдаче предупреждений?";
            $response->setButtonRow(['Убрать', "role_access_message_peer $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_message_peer $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к выдаче предупреждений?";
            $response->setButtonRow(['Предоставить', "role_access_message_peer $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_message_peer $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleMessagePeerAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::PRED_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::PRED_ACCESS);
            }
        }
        if($role->haveAccess(Role::PEER_MESSAGE_ACCESS))
        {
            $response->message = "Убрать доступ к общению между беседами?";
            $response->setButtonRow(['Убрать', "role_access_role_editor $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_role_editor $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к общению между беседами?";
            $response->setButtonRow(['Предоставить', "role_access_role_editor $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_role_editor $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleEditorRoleAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::PEER_MESSAGE_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::PEER_MESSAGE_ACCESS);
            }
        }
        if($role->haveAccess(Role::ROLE_EDITOR_ACCESS))
        {
            $response->message = "Убрать доступ изменения ролей участников?";
            $response->setButtonRow(['Убрать', "role_access_mute $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_mute $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ изменения ролей участников?";
            $response->setButtonRow(['Предоставить', "role_access_mute $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_mute $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleMuteAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::ROLE_EDITOR_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::ROLE_EDITOR_ACCESS);
            }
        }
        if($role->haveAccess(Role::MUTE_ACCESS))
        {
            $response->message = "Убрать доступ к заглушению пользователей?";
            $response->setButtonRow(['Убрать', "role_access_immune $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_immune $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к заглушению пользователей?";
            $response->setButtonRow(['Предоставить', "role_access_immune $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_immune $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleImmuneAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::MUTE_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::MUTE_ACCESS);
            }
        }
        if($role->haveAccess(Role::IMMUNE_ACCESS))
        {
            $response->message = "Убрать доступ к иммунитету от команд?";
            $response->setButtonRow(['Убрать', "role_access_end $role->id no", Response::NEGATIVE],
                ['Пропустить', "role_access_end $role->id", Response::SECONDARY]);
        }
        else
        {
            $response->message = "Предоставить доступ к иммунитету от команд?";
            $response->setButtonRow(['Предоставить', "role_access_end $role->id yes", Response::POSITIVE],
                ['Пропустить', "role_access_end $role->id", Response::SECONDARY]);
        }
        return $response;
    }

    public function accessRoleEndAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $vars = explode(' ', $user_text);
        $role_id = intval($vars[0]);
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Роль не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        if(isset($vars[1]))
        {
            if($vars[1] == 'yes')
            {
                $role->setAccess(Role::MUTE_ACCESS);
            }
            elseif($vars[1] == 'no')
            {
                $role->revokeAccess(Role::MUTE_ACCESS);
            }
        }
        $response->message = "Настройка роли завершена!";
        return $response;
    }
}