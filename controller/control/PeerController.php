<?php


namespace controller\control;

use comboModel\UserPeer;
use core\Controller;
use model\Group;
use model\Role;
use model\User;
use model\Web;
use core\Response;
use model\Peer;
use core\App;

class PeerController extends Controller
{
    public function UpdateWebAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $web = new Web($this->peer->web_id);
        if ($this->user->id == $web->owner_id || $this->user->is_dev == 1) {
            if ($this->peer->init == 1) {
                if(is_null($this->userPeer->getCallback('UpdateWeb', false, $this->peer))) {
                    $this->userPeer->createCallback('UpdateWeb', time());
                    $this->vk->messagesSend($this->peer->id, "Задача по обновлению бесед в сетке поставлена, скоро пройдет обновление");
                }
                else
                {
                    $this->vk->messagesSend($this->peer->id, "Задача по обновлению беседы уже была поставлена, проявите терпение, скоро пройдет обновление");
                }
            }
        } else {
            $user = new User($web->owner_id);
            $response->message = "Вы не являетесь создателем сетки {$web->name}, для обновления напишите {$user->getName()}.";
        }
        return $response;
    }

    public function updateAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->id == $this->peer->owner_id || $this->userPeer->role_id == Role::MAIN_ADMIN || ($this->user->is_dev == 1)) {
            if ($this->peer->init == 1) {
                if(is_null($this->userPeer->getCallback('updateCurrentPeer', false, $this->peer))) {
                    $this->userPeer->createCallback('updateCurrentPeer', time());
                    $this->vk->messagesSend($this->peer->id, "Задача по обновлению беседы поставлена, скоро пройдет обновление");
                }
                else
                {
                    $this->vk->messagesSend($this->peer->id, "Задача по обновлению беседы уже была поставлена, проявите терпение, скоро пройдет обновление");
                }
            } else {
                $response->message = 'Инициализация еще не проводилась, пожалуйста, инициилизируйте беседу (беседа инициализация)';
                $response->setButton('беседа инициализация', 'peer_init');
            }
        }
        return $response;
    }

    public function getSettingsAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $settings = json_decode(file_get_contents('config/settings.json'), true);
        $settingsPeer = $this->peer->getSettings();
        $setsWeb = [];
        if ($this->peer->web_id != 0) {
            $web = new Web($this->peer->web_id);
            $settingsWeb = $web->getSettings();
            foreach ($settingsWeb as $setting)
                $setsWeb[$setting['setting_id']] = $setting['value'];
        }
        $setsPeer = [];
        foreach ($settingsPeer as $setting)
            $setsPeer[$setting['setting_id']] = $setting['value'];
        $message = $this->render('peer/settings', [
            'settings' => $settings,
            'settingsPeer' => $setsPeer,
            'settingsWeb' => $setsWeb
        ]);
        $response->message = $message;
        return $response;
    }

    public function attentionAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = UserPeer::findsByPeerAndUser($this->user->id, $this->peer->id);
        if ($user_text == '') {
            if ($user->check < $this->peer->getSetting(App::S_COUNT_ATTENTION)) {
                if ($this->user->is_dev == 0) {
                    $user->check = $user->check + 1;
                    $user->save();
                }
                $number = $this->peer->getSetting(15) - $user->check;
                $this->vk->messagesSend($this->peer->id, "Вы привлекли внимание пользователей, осталось {$number} использований у данной команды.");
                $response->message = "@all";
            } elseif ($user->check >= $this->peer->getSetting(App::S_COUNT_ATTENTION) && $user->role_id != Role::MAIN_ADMIN && $this->user->is_dev == 0) {
                $this->vk->messagesSend($this->peer->id, "Вы выгнаны из беседы за частое использование команды.");
                $this->vk->messagesRemoveChatUser($this->peer->id, $this->userPeer->user_id);
                $user->deleted = 1;
                $user->save();
            }
        }
        return $response;
    }

    public function autokickOnAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $this->peer->setSetting(App::S_INACTIVE_KICK, 1);
            $this->peer->save();
            $response->message = 'Автокик включен.';
        }
        return $response;
    }

    public function autokickOffAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $this->peer->setSetting(App::S_INACTIVE_KICK, 0);
            $this->peer->save();
            $response->message = 'Автокик отключен.';
        }
        return $response;
    }

    public function KickInactiveAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user_text = intval($user_text);
        $inactive_kick_time = $this->peer->getSetting(App::S_INACTIVE_KICK_TIME);
        if ($this->userPeer->haveAccess(Role::KICK_ACCESS) || $this->user->is_dev == 1) {
            if ($user_text == '') {
                $this->vk->messagesSend($this->peer->id, "Начинаю очистку молчунов за $inactive_kick_time дней...");
                $users = UserPeer::SelectNeedKick($inactive_kick_time, $this->peer->id);
                foreach ($users as $user) {
                    $userPeer = UserPeer::findsByPeerAndUser($user['user_id'], $this->peer->id);
                    $userPeer->deleted = 1;
                    $userPeer->save();
                    $this->vk->messagesRemoveChatUser($this->peer->id, $user['user_id']);
                }
            } elseif ($user_text > 0) {
                $this->vk->messagesSend($this->peer->id, "Начинаю очистку молчунов за {$user_text} дней...");
                $users = UserPeer::SelectNeedKick($user_text, $this->peer->id);
                foreach ($users as $user) {
                    $userPeer = UserPeer::findsByPeerAndUser($user['user_id'], $this->peer->id);
                    $userPeer->deleted = 1;
                    $userPeer->save();
                    $this->vk->messagesRemoveChatUser($this->peer->id, $user['user_id']);
                }
            } else {
                $response->message = "Введите значение больше 0.";
            }
        } else {
            $response->message = "У вас нет доступа к этой команде.";
        }
        $response->message = "Очистка неактива звершена...";
        return $response;
    }

    public function searchAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $peers = Peer::findByName($user_text);
        $str = 'Беседы:'.PHP_EOL;
        foreach ($peers as $peer)
        {
            $id = $peer['id'] - App::$peerStartNumber;
            $str .= "$id) {$peer['title']}".PHP_EOL;
        }
        $response->message = $str;
        return $response;
    }
}