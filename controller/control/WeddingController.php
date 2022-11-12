<?php

namespace controller\control;

use comboModel\UserPeer;
use core\App;
use core\Controller;
use model\User;
use core\Response;
use model\Wedding;
use model\WeddingKids;

class WeddingController extends Controller
{
    public function weddingAction($object, $username)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $word = "-";
        $user = $this->getUserFromMessage($username, $object);
        if (strpos($object['message']['reply_message']['from_id'], $word) !== false) {
            $response->message = "";
        } elseif ($object['message']['reply_message']['from_id'] == $this->user->id) {
            $response->message = "";
        } elseif (isset($object['message']['reply_message'])) {
            $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
            if ($wedding === false) {
                $wedding = Wedding::findByUserId($object['message']['reply_message']['from_id'], $this->peer->id);
                if ($wedding === false) {
                    $wedding = new Wedding();
                    $wedding->sec_user = $object['message']['reply_message']['from_id'];
                    $wedding->first_user = $this->user->id;
                    $wedding->peer_id = $this->peer->id;
                    $wedding->data_tst = 0;
                    $id = $wedding->save();
                    if ($id) {
                        $users = User::findById($object['message']['reply_message']['from_id']);
                        $this->vk->messagesSend($this->peer->id, "Жду подтверждения брака от: " . $users->getName() . PHP_EOL . "('согласен' или 'не согласен'). У него есть 20 секунд иначе брак будет недействителен.");
                        $this->userPeer->createCallback('Wedding', time() + 20, ['user_id' => $users->id]);
                        $response->setButtonRow(['Согласен', '1'], ['Не согласен', '2']);
                    } else
                        $response->message = 'Ошибка!';
                } else
                    $response->message = "Пользователь уже состоит в браке.";
            } else
                $response->message = "Вы уже состоите в браке";
        } elseif ($user->id != $this->user->id) {
            if ($user->isExists()) {
                $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
                if ($wedding === false) {
                    $wedding = Wedding::findByUserId($user->id, $this->peer->id);
                    if ($wedding === false) {
                        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
                        $wedding = new Wedding();
                        $wedding->sec_user = $userPeer->user_id;
                        $wedding->first_user = $this->user->id;
                        $wedding->peer_id = $this->peer->id;
                        $wedding->data_tst = 0;
                        $id = $wedding->save();
                        if ($id) {
                            $users = User::findById($user->id);
                            $this->vk->messagesSend($this->peer->id, "Жду подтверждения брака от: " . $users->getName() . PHP_EOL . "('согласен' или 'не согласен'). У него есть 20 секунд иначе брак будет недействителен.");
                            $this->userPeer->createCallback('Wedding', time() + 20, ['user_id' => $users->id]);
                            $response->setButtonRow(['Согласен', '1'], ['Не согласен', '2']);
                        } else
                            $response->message = 'Ошибка!';
                    } else
                        $response->message = "Пользователь уже состоит в браке.";
                } else
                    $response->message = "Вы уже состоите в браке";
            }
        }
        return $response;
    }

    public function weddingYesAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
        if ($user_text == '' && $this->user->id != $wedding->first_user) {
            $time = time() + 20;
            if ($wedding != false && $wedding->data_tst <= $time) {
                $wedding->data_tst = time() + 30;
                $wedding->save();
                $response->message = "Ваш брак подтверждён.";
            }
        }
        return $response;
    }

    public function removeWeddingAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
        if ($wedding->isExists()) {
            $children = WeddingKids::FindKids($this->user->id, $this->peer->id);
            foreach ($children as $child)
            {
                $obj = new WeddingKids($child['id']);
                $obj->delete();
            }
            $wedding->delete();
            $response->message = "Ваш брак аннулирован.";
        } else {
            $response->message = 'Вы не состоите в браке.';
        }
        return $response;
    }

    public function weddingNoAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
        if ($user_text == '' && $this->user->id != $wedding->first_user) {
            if ($wedding->isExists()) {
                $wedding->delete();
                $response->message = "Увы, брак не состоялся.";
            }
        }
        return $response;
    }

    public function weddingAllAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $weddings = Wedding::weddingAll($this->peer->id);
        if (!empty($weddings)) {
            $message = $this->render('top/weddings', [
                'weddings' => $weddings,
                'title' => 'Браки в данной беседе:'
            ]);
            $response->message = $message;
        } else
            $response->message = "В данной беседе пока что нет браков. Для того чтобы предложить брак пропишите команду брак на сообщение пользователя.";
        return $response;
    }

    public function GetKidsAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $weddings = WeddingKids::FindKids($this->user->id, $this->peer->id);
        if (!empty($weddings)) {
            $message = $this->render('top/weddingKids', [
                'weddings' => $weddings,
                'title' => 'Ваши дети :3'
            ]);
            $response->message = $message;
        } else
            $response->message = "У вас нет детишек в данной беседе:3";
        return $response;
    }

    public function GetParentAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $weddings = WeddingKids::FindParent($this->user->id, $this->peer->id);
            if (!empty($weddings)) {
                $message = $this->render('top/weddingParent', [
                    'weddings' => $weddings,
                    'title' => 'Ваши родители :3'
                ]);
                $response->message = $message;
            } else
                $response->message = "У вас нет родителей в данной беседе:3 Вы в детдоме";
        }
        return $response;
    }

    public function weddingGetAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
        if (!empty($wedding)) {
            $weddings = WeddingKids::FindKids($this->user->id, $this->peer->id);
            $message = $this->render('top/weddingKids', [
                'weddings' => $weddings,
                'title' => 'Ваши дети :3'
            ]);
            $response->message = "Вы состоите в браке с " . (new User($wedding->getPartner()))->getName()
            . PHP_EOL . "{$message}";
        } else
            $response->message = "У вас нет брака в данной беседе.";
        return $response;
    }

    public function SetKidsAction($object, $username = '')
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        $id = $this->getIdFromMessage($object);
        $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
        if ($username == '' && 0 < $id) {
            if ($this->user->id != $id && $id != $wedding->first_user && $id != $wedding->sec_user) {
                if ($wedding != false) {
                    $kids = WeddingKids::findKid($id, $this->peer->id);
                    if ($kids === false) {
                        $kid = new WeddingKids();
                        $kid->user_id = $id;
                        $kid->mother = $wedding->first_user;
                        $kid->father = $wedding->sec_user;
                        $kid->peer_id = $this->peer->id;
                        $kid->sex_tst = 0;
                        $kid->save();
                        $users = new User($id);
                        $users2 = new User($wedding->first_user);
                        $users3 = new User($wedding->sec_user);
                        $response->message = "Ждём согласия от " . $users->getName() . " на то чтоб стать ребёнком " . $users2->getName() ." и " . $users3->getName() . ".";
                        $response->setButtonRow(['Да', '1'], ['Нет', '2']);
                        $this->userPeer->createCallback('Kid', time() + 20, ['user_id' => $id]);
                    } else
                        $response->message = "Он или она уже является сыном/дочкой ^_^";
                } else
                    $response->message = "Необходим брак для данной команды :3 пропишите команду брак и перешлите сообщения пользователя.";
            }
        } elseif (isset($user) && $user->isExists()) {
            $wedding = Wedding::findByUserId($this->user->id, $this->peer->id);
            if ($user !== false && $user->isExists()) {
                if ($this->user->id != $user->id && $user->id != $wedding->sec_user && $user->id != $wedding->first_user) {
                    if ($wedding != false) {
                        $kids = WeddingKids::FindKid($user->id, $this->peer->id);
                        if ($kids === false) {
                            $kid = new WeddingKids();
                            $kid->user_id = $user->id;
                            $kid->mother = $wedding->first_user;
                            $kid->father = $wedding->sec_user;
                            $kid->peer_id = $this->peer->id;
                            $kid->sex_tst = 0;
                            $kid->save();
                            $users2 = new User($wedding->first_user);
                            $users3 = new User($wedding->sec_user);
                            $response->message = "Ждём согласия от " . $user->getName() . " на то чтоб стать ребёнком " . $users2->getName() ." и " . $users3->getName() . ".";
                            $response->setButtonRow(['Да', '1'], ['Нет', '2']);
                            $this->userPeer->createCallback('Kid', time() + 20, ['user_id' => $user->id]);
                        } else
                            $response->message = "Он или она уже является сыном/дочкой ^_^";
                    } else
                        $response->message = "Необходим брак для данной команды :3 пропишите команду брак и перешлите сообщения пользователя.";
                }
            }
        }
        return $response;
    }

    public function FreeChildAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $kid = WeddingKids::FindKid($this->user->id, $this->peer->id);
        if($kid === false)
        {
            $response->message = 'Ошибочка, вы уже в детдоме';
            return $response;
        }
        $res = $kid->delete();
        if($res !== false)
            $response->message = 'Вы успешно ушли в детдом';
        else
            $response->message = 'Ошибочка, обратитесь к разрабу';
        return $response;
    }

    public function AnswerKidYesAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $kid = WeddingKids::FindKidNew($this->user->id, $this->peer->id);
        if ($kid !== false && $this->user->id == $kid->user_id) {
            $kid->sex_tst = time();
            $kid->save();
            $users = new User($kid->user_id);
            $users2 = new User($kid->mother);
            $users3 = new User($kid->father);
            $response->message = $users->getName() . " согласился стать сыном или дочкой " . $users2->getName() ." и " . $users3->getName() . ".";
        }
        return $response;
    }

    public function AnswerKidNoAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $kid = WeddingKids::FindKidNew($this->peer->id, $this->peer->id);
        if ($kid !== false && $this->user->id == $kid->user_id) {
            $kid->delete();
            $users = new User($kid->user_id);
            $users2 = new User($kid->mother);
            $users3 = new User($kid->father);
            $response->message = $users->getName() . " отказался стать сыном или дочкой " . $users2->getName() ." и " . $users3->getName() . ".";
        }
        return $response;
    }
}