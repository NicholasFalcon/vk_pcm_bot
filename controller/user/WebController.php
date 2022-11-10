<?php

namespace controller\user;

use core\Controller;
use core\Response;
use model\User;
use model\Web;

class WebController extends Controller
{
    public function createAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $count = Web::getCountByOwnerId($this->user->id);
        if($count >= 5)
        {
            $response->message = 'Вы достигли ограничения на создание сеток';
            return $response;
        }
        $response->message = 'Новая сетка создана, введите следующим сообщением название новой сетки:';
        $web = new Web();
        $web->owner_id = $this->user->id;
        $web->name = 'Новая сетка';
        $web->save();
        $id = Web::getLastId("`owner_id` = {$this->user->id}");
        $this->user->user_action = "editWebName $id";
        $this->user->save();
        return $response;
    }

    public function editAction($web_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $web = Web::findById(intval($web_id));
        if($web === false)
        {
            $response->message = 'Ошибка системы, скорее всего данная сетка уже удалена';
            return $response;
        }
        $response->message = $this->render('web/owner_info', [
            'web' => $web,
        ]);
        $response->setButtonRow(['Добавить администратора', "web_add_admin $web->id", Response::POSITIVE]);
        $response->setButtonRow(['Удалить администратора', "web_del_admin $web->id", Response::NEGATIVE]);
        $response->setButtonRow(['Изменить название', "web_title $web->id", Response::PRIMARY]);
        $response->setButtonRow(['Удалить', "web_delete $web->id", Response::NEGATIVE]);
        return $response;
    }

    public function deleteAction($web_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $web = Web::findById(intval($web_id));
        if($web === false)
        {
            return $response;
        }
        $web->delete();
        $response->message = 'Сетка удалена';
        return $response;
    }

    public function titleAction($web_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $web = Web::findById(intval($web_id));
        if($web === false)
        {
            $response->message = 'Сетка не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        $response->message = 'Введите следующим сообщением название сетки:';
        $this->user->user_action = "editWebName $web->id";
        $this->user->save();
        return $response;
    }

    public function addAdminAction($web_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $web = Web::findById(intval($web_id));
        if($web === false)
        {
            $response->message = 'Сетка не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        $response->message = 'Введите следующим сообщением идентификацию пользователя (ссылка, id, упоминание):';
        $this->user->user_action = "webAddAdmin $web->id";
        $this->user->save();
        return $response;
    }

    public function delAdminAction($web_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->user->id;
        $web = Web::findById(intval($web_id));
        if($web === false)
        {
            $response->message = 'Сетка не обнаружена, скорее всего она уже удалена';
            return $response;
        }
        $response->message = 'Введите следующим сообщением идентификацию пользователя (ссылка, id, упоминание):';
        $this->user->user_action = "webDelAdmin $web->id";
        $this->user->save();
        return $response;
    }
}