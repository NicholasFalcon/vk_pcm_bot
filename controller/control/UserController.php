<?php


namespace controller\control;

use comboModel\UserPeer;
use core\App;
use core\Controller;
use model\Peer;
use model\Role;
use model\User;
use core\Response;

class   UserController extends Controller
{
    public function getAction($username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $regUser = "~\[id(?<id>[0-9]*)\|[^\[\]\|]*\]~";
        $user = $this->getObjFromMessage($username);
        if ($user !== false && $user->isUser()) {
            $response->message = $this->renderProfile($user);
        } else {
            $user = User::findByNick($username);
            if ($user->isExists()) {
                $response->message = $this->renderProfile($user);
            } else
                $response->message = 'Пользователь не найден';
        }
        if ($response->message == '')
            $response->message = 'Аргумент неверный';
        return $response;
    }

    public function getReplyAction($object): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user_id = $this->getIdFromMessage($object);
        if ($user_id > 0) {
            $user = User::findById($user_id);
            if ($user != false) {
                $response->message = $this->renderProfile($user);
            }
        }
        if($response->message == '')
        {
            $response->message = 'Я не знаю данного пользователя!';
        }
        return $response;
    }

    private function renderProfile($user)
    {
        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
        if ($userPeer == false) {
            $userPeer = new UserPeer();
            $userPeer->peer_id = $this->peer->id;
            $userPeer->user_id = $user->id;
            $userPeer->have_ban = 0;
            $userPeer->role_id = 1;
            $userPeer->save();
        }
        return $this->render('user/profile', [
            'user' => $user,
            'userPeer' => $userPeer,
            'level' => $userPeer->level,
            'peer' => $this->peer
        ]);
    }

    public function TPAction($user_text): Response
    {
        $response = new Response();
        if ($user_text == '')
            $response->message = 'Eсли есть вопросы по функционалу бота или имеются ошибки в работе бота можно и нужно написать им: '
                . PHP_EOL . '1. [hironori|Николай] (Отвечу всем, team lead bot developer)'
                . PHP_EOL . '2. [eoremic|Антон] (В сети почти всегда, senior bot developer)';
        $response->peer_id = $this->peer->id;
        return $response;
    }

//    public function whoAction($user_text)
//    {
//        $response = new Response();
//        $response->peer_id = $this->peer->id;
//        $user_id = $this->userPeer->getRandomUser();
//        $user = new User($user_id);
//        $response->message = "{$this->user->getName()}, да ты что, $user_text - {$user->getName()}";
//        return $response;
//    }

    public function MyPeersAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $number = 0;
        $answer = '';
        $peer = Peer::PeerByUser($this->user->id);
        while ($number != count($peer)) {
            $ts = $peer[$number]['id'] - 2000000000;
            $number++;
            $answer .= $number . ") Беседа {$peer[$number]['title']} имеет id = {$ts}" . PHP_EOL;
        }
        $response->message = "Беседы:" . PHP_EOL . $answer;
        return $response;
    }

    public function typesAction($text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $numb = rand(0, 100);
        $response->message = "{$this->user->getName()}, '$text' верно на $numb%";
        return $response;
    }

    public function getMyAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = $this->render('user/profile', [
            'user' => $this->user,
            'userPeer' => $this->userPeer,
            'level' => $this->userPeer->level,
            'peer' => $this->peer
        ]);
        return $response;
    }

    public function SetNickAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if (mb_strlen($user_text) < 20) {
            $nick = User::findByNick($user_text);
            if ($nick === false) {
                $this->user->nick = $user_text;
                $this->user->save();
                $response->message = "Ник пользователя изменен на $user_text";
            } else
                $response->message = "Данный ник уже занят!";
        } else
            $response->message = "Длина ника слишком большая!";
        return $response;
    }

//    public function RewardSetAction($user_text, $object)
//    {
//        $response = new Response();
//        $response->peer_id = $this->peer->id;
//        $id = $this->getIdFromMessage($object);
//        if (isset($object['message']['reply_message'])) {
//            if ($this->userPeer->status >= $this->peer->getSetting(2) || $this->user->is_dev == 1) {
//                $userPeer = UserPeer::findsByPeerAndUser($id, $this->peer->id);
//                if ($user_text != '') {
//                    if ($this->user->id != $userPeer->user_id) {
//                        $reward = Rewards::findGame()
//                        if ($user_text != $reward->title) {
//
//                        } else {
//                            $response->message = "У пользователя уже есть такая награда в данной беседе.";
//                        }
//                    }
//                } else {
//                    $response->message = "Введите текст награды";
//                }
//                if ($this->peer->getSetting(16) == 1)
//                    $response->message = "Ваш статус не позволяет пользоваться данной командой. Необходимый статус {$this->peer->getSetting(2)}";
//            } else
//                if ($this->peer->getSetting(16) == 1)
//                    $response->message = "Выберите сообщение человека, которому необходимо выдать награду.";
//        }
//        return $response;
//    }

    public function SetPinAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if (mb_strlen($user_text) < 2) {
            $this->user->pin = $user_text;
            $this->user->save();
            $response->message = "Значок пользователя изменен на $user_text";
        } else
            $response->message = "Длина значка слишком большая!";
        return $response;
    }

    public function AudioMesAction($user_text, $object)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
//        if ($user_text != '') {
//            $response->message = print_r($object,1);
//            $response->setList("рпвапрвпар");
//        }
        return $response;
    }

    public function shutUpAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $this->user->is_callable = 0;
        $this->user->save();
        $response->message = "Бот не будет вас упоминать, кроме созвать всех)";
        return $response;
    }

    public function callMeAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $this->user->is_callable = 1;
        $this->user->save();
        $response->message = "Бот будет вас упоминать";
        return $response;
    }

    public function getInactiveAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN) {
            $userInfo = UserPeer::findInactive($this->peer->id);
            $message = $this->render('top/inactive', [
                'userInfo' => $userInfo,
                'title' => 'Неактивные пользователи:',
                'timeInactive' => (time() - $this->peer->getSetting(App::S_INACTIVE_KICK_TIME) * 3600 * 24)
            ]);
            $response->message = $message;
        } else
            $response->message = "У вас не хватает статуса для этой команды.";
        return $response;
    }

    public function GetNickAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1) {
            $userInfo = User::findNick();
            $message = $this->render('top/nick', [
                'userInfo' => $userInfo,
                'title' => 'Ники пользователей:'
            ]);
            $response->message = $message;
        }
        return $response;
    }

    /**
     *
     * Поиск чего-то по запрсоу пользака
     *
     */
    public function SearchAction($doc_name)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1) {
            $num = 0;
            while ($num != 1) {
                $num++;
                $search = $this->vk->docsSearch($doc_name, $num, 5);
                foreach ($search as $result) {
                    foreach ($result['items'] as $item) {
                        sleep(1);
                        $this->vk->messagesSend($this->peer->id, "", "doc{$item['owner_id']}_{$item['id']}");
                    }
                }
            }
        }
        return $response;
    }

    public function translateTextAction($object)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($object['message']['reply_message']['text']) {
            $rus = array('q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '[', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', ';', "'", 'z', 'x', 'c', 'v', 'b', 'n', 'm', ',', '.', ']');
            $lat = array('й', 'ц', 'у', 'к', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э', 'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю', 'ъ');
            $text = str_replace($rus, $lat, mb_strtolower($object['message']['reply_message']['text']));
            $response->message = $text;
        }
        return $response;
    }
}