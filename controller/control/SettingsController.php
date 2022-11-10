<?php


namespace controller\control;

use core\Controller;
use core\Response;
use model\Role;

class SettingsController extends Controller
{
    public function changeAction($setting_id, $value): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || ($this->user->is_dev == 1)) {
            $info = $this->peer->setSetting($setting_id, $value);
            if ($info == 'error_type')
                $response->message = 'Значение настройки указано неверно! (обычно 1 - включить, 0 - выключить)';
            elseif ($info == 'error_not_found')
                $response->message = 'Настройка не найдена';
            elseif ($info)
                $response->message = 'Успешно!';
            elseif ($response->message == '')
                $response->message = 'Ошибка! Обратитесь к разработчику';
        }
        return $response;
    }
}