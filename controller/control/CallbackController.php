<?php


namespace controller\control;

use comboModel\UserPeer;
use core\Controller;
use model\Peer;
use model\User;
use model\Web;
use model\Wedding;
use model\WeddingKids;
use core\Response;
use core\App;

class CallbackController extends Controller
{
    public function unmutePeerAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->peer->MutePeer == 1) {
            $this->peer->MutePeer = 0;
            $this->peer->save();
            $response->message = "В беседе снят тихий час. Все участники снова могут общаться.";
        }
        return $response;
    }

    public function unmuteUserAction($user_id): Response
    {
        $userPeer = UserPeer::findsByPeerAndUser($user_id, $this->peer->id);
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if($userPeer->muted == 1)
        {
            $response->message = "[id$userPeer->user_id|Пользователь] больше не заглушен. Впредь не хулиганьте.";
            $userPeer->muted = 0;
            $userPeer->save();
        }
        return $response;
    }

    public function KidAction($user_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $kid = WeddingKids::FindKid($user_id,$this->peer->id);
        $new_kid = new WeddingKids($kid['0']['id']);
        if($new_kid->sex_tst == 0)
        {
            $new_kid->delete();
            $user = new User($user_id);
            $response->message = $user->getName() . " не дал своё согласие быть ребёнком.";
        }
        return $response;
    }

    public function WeddingAction($user_id): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $wedding = Wedding::findByUserId($user_id, $this->peer->id);
        if($wedding->data_tst == 0)
        {
            $wedding->delete();
            $user = new User($user_id);
            $response->message = $user->getName() . " не дал своё согласие на вступление в брак.";
        }
        return $response;
    }

    public function updateCurrentPeerAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $this->vk->messagesSend($this->peer->id, "Время обновления зависит от количества человек...");
        $result = App::updatePeer($this->peer->id);
        if ($result !== false) {
            App::updateUsers($this->peer);
            $response->message = 'Обновление успешно';
        } else {
            $response->message = 'Обновить не удалось, обратитесь к разработчику';
        }
        return $response;
    }

    public function UpdateWebAction(): Response
    {
        $response = new Response();
        $this->vk->messagesSend($this->peer->id, "Стартовала задача по обновлению бесед в сетке");
        $response->peer_id = $this->peer->id;
        $web = new Web($this->peer->web_id);
        $peers = $web->getPeersIds();
        foreach ($peers as $peer) {
            $obj = new Peer($peer['id']);
            $this->vk->messagesSend($obj->id, "Началось обновление беседы. Время обновления зависит от количества человек...");
            $this->vk->messagesSend($this->peer->id, "Обновляется беседа - ".$obj->title);
            $result = App::updatePeer($obj->id);
            if ($result !== false) {
                App::updateUsers($obj);
                $this->vk->messagesSend($obj->id, "Обновление успешно.");
            } else {
                $this->vk->messagesSend($obj->id, "Обновление не удалось, напишите разработчику бота.");
            }
        }
        $response->message = "Обновление сетки успешно завершено.";
        return $response;
    }

    public function sendHelloMessageAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->peer->getSetting(19) == 1) {
            $response->message = $this->peer->HelloMessage;
            if($this->peer->getSetting(20) == 1 && $this->peer->rules != '')
            {
                $response->message .= PHP_EOL . "Правила беседы:" . PHP_EOL . $this->peer->rules;
            }
        }
        return $response;
    }
}