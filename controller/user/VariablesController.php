<?php

namespace controller\user;

use core\Controller;
use core\Response;
use model\Role;
use model\Web;

class VariablesController extends Controller
{
    public function editRoleTitleAction($user_text, $variables): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        if(count($variables) != 1)
        {
            $response->message = 'Ошибка!!!! Код ошибки: edit_role_count, сообщите его разработчику';
            return $response;
        }
        $role_id = intval($variables[1]);
        if(!is_int($role_id))
        {
            $response->message = 'Ошибка!!!! Код ошибки: edit_role_variable, сообщите его разработчику';
            return $response;
        }
        $role = Role::findById($role_id);
        if($role === false)
        {
            $response->message = 'Ошибка!!!! Код ошибки: edit_role_undefined, сообщите его разработчику';
            return $response;
        }
        $role->title = $user_text;
        $role->save();
        $response->message = 'Название роли присвоено';
        return  $response;
    }

    public function editWebNameAction($user_text, $variables): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        if(count($variables) != 1)
        {
            $response->message = 'Ошибка!!!! Код ошибки: edit_web_count, сообщите его разработчику';
            return $response;
        }
        $web_id = intval($variables[1]);
        if(!is_int($web_id))
        {
            $response->message = 'Ошибка!!!! Код ошибки: edit_web_variable, сообщите его разработчику';
            return $response;
        }
        $web = Web::findById($web_id);
        if($web === false)
        {
            $response->message = 'Ошибка!!!! Код ошибки: edit_web_undefined, сообщите его разработчику';
            return $response;
        }
        $web->name = $user_text;
        $web->save();
        $response->message = 'Название сетки присвоено';
        return  $response;
    }

    public function webAddAdminAction($user_text, $variables): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        if(count($variables) != 1)
        {
            $response->message = 'Ошибка!!!! Код ошибки: web_add_admin_count, сообщите его разработчику';
            return $response;
        }
        $web_id = intval($variables[1]);
        if(!is_int($web_id))
        {
            $response->message = 'Ошибка!!!! Код ошибки: web_add_admin_variable, сообщите его разработчику';
            return $response;
        }
        $web = Web::findById($web_id);
        if($web === false)
        {
            $response->message = 'Ошибка!!!! Код ошибки: web_add_admin_undefined, сообщите его разработчику';
            return $response;
        }
        $user = $this->getObjFromMessage($user_text);
        if($user === false || !$user->isUser())
        {
            $response->message = 'Пользователь не найден в базе!';
            return  $response;
        }
        $web->setAdmin($user);
        $response->message = 'Администратор присвоен сетке';
        return  $response;
    }

    public function webDelAdminAction($user_text, $variables): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        if(count($variables) != 1)
        {
            $response->message = 'Ошибка!!!! Код ошибки: web_del_admin_count, сообщите его разработчику';
            return $response;
        }
        $web_id = intval($variables[1]);
        if(!is_int($web_id))
        {
            $response->message = 'Ошибка!!!! Код ошибки: web_del_admin_variable, сообщите его разработчику';
            return $response;
        }
        $web = Web::findById($web_id);
        if($web === false)
        {
            $response->message = 'Ошибка!!!! Код ошибки: web_del_admin_undefined, сообщите его разработчику';
            return $response;
        }
        $user = $this->getObjFromMessage($user_text);
        if($user === false || !$user->isUser())
        {
            $response->message = 'Пользователь не найден в базе!';
            return  $response;
        }
        $web->unsetAdmin($user);
        $response->message = 'Администратор отвязан от сетки';
        return  $response;
    }
}