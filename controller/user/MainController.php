<?php

namespace controller\user;

use core\Controller;
use core\Response;
use model\Role;
use model\Web;

class MainController extends Controller
{
    public function startAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $response->message = 'Вас приветствует команда разработки бота "Карманный чат менеджер". Выберите пункты меню:';
        $response->setButtonFull();
        $response->setButtonRow(["Беседы", "menu1"], ["Настроить роли", "menu2"]);
        $response->setButtonRow(["Настроить сетки", "menu3"], ["Пункт меню 4", "menu4"]);
        return $response;
    }

    public function menu1Action(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $response->message = 'Пришлите номер беседы, для ';
        return $response;
    }

    public function menu2Action(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $response->message = 'Ваши роли:';
        $roles = Role::findAllByOwnerId($this->user->id);
        $counter = 1;
        foreach ($roles as $role)
        {
            $response->message .= PHP_EOL . "$counter) {$role['title']}";
            $response->setButtonRow(["Изменить роль {$role['title']}", "edit_role {$role['id']}"]);
        }
        $response->setButtonRow(["Создать новую роль", "create_role"]);
        return $response;
    }

    public function menu3Action(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $webs = Web::findAllByOwnerId($this->user->id);
        $response->message = 'Ваши сетки:';
        $counter = 1;
        foreach ($webs as $web)
        {
            $response->message .= PHP_EOL . "$counter) {$web['name']}";
            $response->setButtonRow(["Изменить сетку {$web['name']}", "edit_web {$web['id']}"]);
        }
        $response->setButtonRow(["Создать новую сетку", "create_web"]);
        return $response;
    }

    public function menu4Action(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $response->message = 'Пусто';
        return $response;
    }
}