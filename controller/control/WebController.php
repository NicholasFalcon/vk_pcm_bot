<?php


namespace controller\control;

use comboModel\UserPeer;
use core\App;
use core\Controller;
use model\Peer;
use model\User;
use model\UserConfirmation;
use model\Web;
use core\Response;

class WebController extends Controller
{
    /**
     * @return Response
     * Вывод текущей сетки бесед
     */
    public function getAction(): Response
    {
        $response = new Response();
        $peer = new Peer($this->peer->id);
        $response->peer_id = $this->peer->id;
        $web = Web::findById($this->peer->web_id);
        if ($web !== false) {
            $user = new User($peer->owner_id);
            if ($peer->owner_id > 0) {
                $owner = $user->getName();
            } else {
                $text = "[club" . $peer->owner_id . "|Группа]";
                $owner = str_replace('-', '', $text);
            }
            $response->message = $this->render('web/info', [
                'web' => $web,
                'webCreator' => new User($web->owner_id),
                'owner' => $owner
            ]);
            $response->setButtonRow(['Сетка инфо', 'web_info', Response::PRIMARY]);
            $response->setButtonRow(["Сетка отвязать", "web_disconnect", Response::NEGATIVE]);
        } else {
            $response->message = 'Беседа еще не привязана к сетке!';
            $response->setButtonRow(["Сетка привязать", "web_add_peer", Response::POSITIVE]);
        }
        return $response;
    }

    /**
     * @return Response
     * Вывод списка сетей пользователя
     *
     */
    public function listWebAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $webs = Web::webList($this->user->id);
        $response->message = $this->render('admin/web_list', [
            'webs' => $webs
        ]);
        return $response;
    }

    public function peerAddWebAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $webs = Web::findAllByOwnerId($this->user->id);
        if (count($webs) > 0) {
            $response->message = "Привязать сетку:";
            foreach ($webs as $web) {
                $response->setButtonRow(["Изменить сетку на {$web['name']}", "web_connect {$web['id']}", Response::POSITIVE]);
            }
            return $response;
        } else
            $response->message = 'У вас сеток то нет!';
        return $response;
    }

    public function connectAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $web_id = intval($user_text);
        $web = new Web($web_id);
        if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1))) {
            if ($this->peer->owner_id == $this->user->id || ($this->user->is_dev == 1)) {
                $this->peer->web_id = $web->id;
                $id = $this->peer->save();
                if ($id)
                    $response->message = 'Успешно привязана';
                else
                    $response->message = 'Ошибка!';
            } else {
                $confirmation = new UserConfirmation();
                $confirmation->user_id = $this->peer->owner_id;
                $confirmation->peer_id = $this->peer->id;
                $confirmation->type_id = 0;
                $confirmation->tst = time();
                $confirmation->setData([
                    'web_id' => $web->id
                ]);
                $confirmation->save();
                $confirmation = UserConfirmation::findByUserIdAndPeerId($this->peer->owner_id, $this->peer->id);
                $owner = User::findById($this->peer->owner_id);
                $response->message = "{$owner->getFullName('nom', true)} подтвердите привязку беседы к сетке {$web->name}";
                $response->setButtonRow(["Подтверждаю {$confirmation->id}", "accept_{$confirmation->id}"], ["Отказываюсь {$confirmation->id}", "decline_{$confirmation->id}"]);
            }
        } else
            $response->message = 'Выбранная секта не найдена, либо вы не создатель!';
        return $response;
    }

    public function disconnectAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $this->peer->web_id = 0;
        $this->peer->save();
        $response->message = 'Сетка отвязана!';
        return $response;
    }

    public function webSettingsAction($user_text)
    {
        $web_id = $user_text;
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $settings = json_decode(file_get_contents('config/settings.json'), true);
        $web = new Web($web_id);
        if ($web->isExists() && $web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user)) {
            $settingsWeb = $web->getSettings();
            $setsWeb = [];
            foreach ($settingsWeb as $setting)
                $setsWeb[$setting['setting_id']] = $setting['value'];
            $message = $this->render('web/settings', [
                'settings' => $settings,
                'settingsWeb' => $setsWeb
            ]);
            $response->message = $message;
        } else
            $response->message = 'Такой сетки не существует';
        return $response;
    }

    public function webChangeSettingAction($user_text)
    {
        $data = explode(' ', $user_text);
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if (count($data) == 3) {
            $web = new Web($data[0]);
            if ($web->isExists() && $web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user)) {
                $info = $web->setSetting($data[1], $data[2]);
                if ($info == 'error_type')
                    $response->message = 'Значение настройки указано неверно! (обычно 1 - включить, 0 - выключить)';
                elseif ($info == 'error_not_found')
                    $response->message = 'Настройка не найдена';
                elseif ($info)
                    $response->message = 'Успешно!';
                elseif ($response->message == '')
                    $response->message = 'Ошибка! Обратитесь к разработчику';
            } else
                $response->message = 'Такой сетки не существует';
        } else
            $response->message = 'Параметры не верны, проверьте написание команды';
        return $response;
    }

    public function removeGlobanAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $web = new Web($this->peer->web_id);
        if ($web->isExists()) {
            if ($this->user->id == $web->owner_id || $this->user->is_dev == 1 || $web->isAdmin($this->user)) {
                $peers = $web->getPeersIds();
                $user = $this->getUserFromMessage($user_text);
                if (get_class($user) == self::$classUser)
                    if ($user->isExists()) {
                        if ($this->user->id == $web->owner_id || $this->user->is_dev == 1 || $web->isAdmin($this->user)) {
                            $web->unsetBan($user->id);
                            foreach ($peers as $peer) {
                                $peer_id = $peer['id'];
                                if ($this->user->is_dev == 1)
                                    $nick = 'Разработчиком бота';
                                elseif ($this->user->id == $web->owner_id)
                                    $nick = 'Создателем сетки';
                                else
                                    $nick = 'Администратором сетки';
                                $this->vk->messagesSend($peer_id, "[id{$user->id}|Пользователь] был глобально разбанен [id{$this->user->id}|{$nick}] из беседы {$this->peer->title}, добавьте его кто-нибудь");
                            }
                            $response->message = 'Пользователь разбанен!';
                        }
                    }
            } else
                $response->message = 'Вы не админ';
        }
        return $response;
    }

    public function globanAction($object, $user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $web = new Web($this->peer->web_id);
        if ($web->isExists()) {
            if ($this->user->id == $web->owner_id || $this->user->is_dev == 1 || $web->isAdmin($this->user)) {
                $peers = $web->getPeersIds();
                $user = new User(0);
                $obj = $this->getUserFromMessage($user_text);
                $id = $this->getIdFromMessage($object);
                if ($obj != false && get_class($obj) == self::$classUser) {
                    $user = $obj;
                }
                if ($user_text == '' && $id > 0) {
                    $dev = new User($id);
                    if ($dev->is_dev == 0 && $dev->isExists()) {
                        if ($dev->id != $web->owner_id) {
                            $web->count_globans = $web->count_globans + 1;
                            $web->save();
                            $web->setBan($id);
                            foreach ($peers as $peer) {
                                $peer_id = $peer['id'];
                                $userPeer = UserPeer::findsByPeerAndUser($id, $peer_id);
                                if ($this->user->is_dev == 1)
                                    $nick = 'Разработчика бота';
                                elseif ($this->user->id == $web->owner_id)
                                    $nick = 'Создателя сетки';
                                else
                                    $nick = 'Администратора сетки';
                                $this->vk->messagesSend($peer_id, "[id{$object['message']['reply_message']['from_id']}|Пользователь] получил глобальный бан от [id{$this->user->id}|{$nick}] из беседы {$this->peer->title}.");
                                $this->vk->messagesRemoveChatUser($peer_id, $userPeer->user_id);
                            }
                        } else
                            $response->message = "Не могу забанить создателя сетки!";
                    } else
                        $response->message = "Не могу забанить СОЗДАТЕЛЯ БОТА!";
                } elseif ($user->isExists() && $user->isUser()) {
                    if ($user->is_dev == 0) {
                        $web->count_globans = $web->count_globans + 1;
                        $web->save();
                        $web->setBan($user->id);
                        foreach ($peers as $peer) {
                            $peer_id = $peer['id'];
                            if ($this->user->is_dev == 1)
                                $nick = 'Разработчика бота';
                            elseif ($this->user->id == $web->owner_id)
                                $nick = 'Создателя сетки';
                            else
                                $nick = 'Администратора сетки';
                            $this->vk->messagesSend($peer_id, "[id{$user->id}|Пользователь] получил глобальный бан от [id{$this->user->id}|{$nick}] из беседы {$this->peer->title}.");
                            $this->vk->messagesRemoveChatUser($peer_id, $user->id);
                        }
                    } else
                        $response->message = "Не могу забанить СОЗДАТЕЛЯ БОТА!";
                } else {
                    if ($user_text != '' && intval($user_text) == $user_text) {
                        $user = new User();
                        $user->id = intval($user_text);
                        $user->save();
                        \App::updateInfoUser($user);
                        $web->count_globans = $web->count_globans + 1;
                        $web->save();
                        $web->setBan($user->id);
                        foreach ($peers as $peer) {
                            $peer_id = $peer['id'];
                            if ($this->user->is_dev == 1)
                                $nick = 'Разработчика бота';
                            elseif ($this->user->id == $web->owner_id)
                                $nick = 'Создателя сетки';
                            else
                                $nick = 'Администратора сетки';
                            $this->vk->messagesSend($peer_id, "[id{$user->id}|Пользователь] получил глобальный бан от [id{$this->user->id}|{$nick}] из беседы {$this->peer->title}.");
                        }
                    } elseif ($user_text != '')
                        $response->message = 'Не могу найти или создать данного пользователя, попробуйте указать только id';
                }
            } else
                $response->message = 'Вы не создатель этой сетки!';
        }
        return $response;
    }

    public function sendGlomessageAction($user_text, $object)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text != '') {
            $web = new Web($this->peer->web_id);
            if ($web->isExists()) {
                if ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user)) {
                    $web = new Web($this->peer->web_id);
                    $peers = $web->getPeersIds();
                    foreach ($peers as $peer) {
                        $peer_id = $peer['id'];
                        if ($this->user->is_dev == 1) $nick = 'Разработчика бота'; else $nick = 'Администратора сетки';
                        if (isset($object['message']['attachments'])) {
                            $attach = $object['message']['attachments'][0];
                            $type = $attach['type'];
                            if (isset($attach[$type]['from_id']))
                                $owner_id = $attach[$type]['from_id'];
                            elseif (isset($attach[$type]['owner_id']))
                                $owner_id = $attach[$type]['owner_id'];
                            else
                                $owner_id = 0;
                            $this->vk->messagesSend($peer_id, $user_text . PHP_EOL . "P.s. Из беседы {$this->peer->title} от [id{$this->user->id}|$nick]", "{$attach['type']}{$owner_id}_{$attach[$type]['id']}");
                        } else
                            $this->vk->messagesSend($peer_id, $user_text . PHP_EOL . "P.s. Из беседы {$this->peer->title} от [id{$this->user->id}|$nick]");
                    }
                    $response->message = 'Глобальное сообщение отправлено';
                } else
                    $response->message = 'Вы не создатель этой сетки';

            } else
                $response->message = "Сетка не привязана!";
        }
        return $response;
    }

    public function deleteAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text)) {
            $web = new Web(intval($user_text));
            if ($web->isExists() && $web->owner_id == $this->user->id) {
                $res = Peer::removeWeb($web->id);
                $web->delete();
                if ($web !== false)
                    $response->message = 'Успешно!';
                else
                    $response->message = 'Ошибка! Обратитесь к разработчику!';
            } else
                $response->message = 'Вы не создатель данной сетки!';
        } else
            $response->message = 'Введён не тот номер!';
        return $response;
    }

    public function peerListAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text)) {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1))) {
                $peers = Peer::findByWeb($web->id);
                $list = "Список бесед привязанных к сетке:" . PHP_EOL;
                $number = 1;
                foreach ($peers as $peer) {
                    $list .= "$number) {$peer['title']}" . PHP_EOL;
                    $number++;
                }
                $response->message = $list;
            } else
                $response->message = 'Вы не создавали данную сетку!';
        } else
            $response->message = 'Номер сетки введён неправильно!';
        return $response;
    }

    public function allAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text) && $user_text != '') {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $users = $this->userPeer->topInfoWeb($web->id);
                $message = $this->render('top/list', [
                    'users' => $users,
                    'title' => 'Топ пользователей за все время по всей сетке(символы | сообщения):'
                ]);
                $response->message = $message;
            }
        } else {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $users = $this->userPeer->topInfoWeb($web->id);
                $message = $this->render('top/list', [
                    'users' => $users,
                    'title' => 'Топ пользователей за все время по всей сетке(символы | сообщения):'
                ]);
                $response->message = $message;
            }
        }
        return $response;
    }

    public function dayAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text) && $user_text != '') {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $users = $this->userPeer->topInfoWeb($web->id, 1);
                $message = $this->render('top/list', [
                    'users' => $users,
                    'title' => 'Топ пользователей за день по всей сетке(символы | сообщения):'
                ]);
                $response->message = $message;
            }
        } else {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $users = $this->userPeer->topInfoWeb($web->id, 1);
                $message = $this->render('top/list', [
                    'users' => $users,
                    'title' => 'Топ пользователей за день по всей сетке(символы | сообщения):'
                ]);
                $response->message = $message;
            }
        }
        return $response;
    }

    public function weekAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text) && $user_text != '') {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $users = $this->userPeer->topInfoWeb($web->id, 7);
                $message = $this->render('top/list', [
                    'users' => $users,
                    'title' => 'Топ пользователей на этой недели по всей сетке(символы | сообщения):'
                ]);
                $response->message = $message;
            }
        } else {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $users = $this->userPeer->topInfoWeb($web->id, 7);
                $message = $this->render('top/list', [
                    'users' => $users,
                    'title' => 'Топ пользователей на этой недели по всей сетке(символы | сообщения):'
                ]);
                $response->message = $message;
            }
        }
        return $response;
    }

    public function peerAllAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text) && $user_text != '') {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $peers = $this->userPeer->topInfoPeer($web->id);
                $message = $this->render('top/peers_list', [
                    'peers' => $peers,
                    'title' => 'Топ 15 бесед в сетке за всё время (символы | сообщения):'
                ]);
                $response->message = $message;
            } else
                $response->message = 'Такой сетки не существует!';
        } else {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && ($this->userPeer->status >= 4 || $this->user->is_dev == 1) || $web->isAdmin($this->user)) {
                $peers = $this->userPeer->topInfoPeer($web->id);
                $message = $this->render('top/peers_list', [
                    'peers' => $peers,
                    'title' => 'Топ 15 бесед в сетке за всё время (символы | сообщения):'
                ]);
                $response->message = $message;
            } else
                $response->message = 'Такой сетки не существует!';
        }
        return $response;
    }

    public function peerDayAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text) && $user_text != '') {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $peers = $this->userPeer->topInfoPeer($web->id, 1);
                $message = $this->render('top/peers_list', [
                    'peers' => $peers,
                    'title' => 'Топ 15 бесед в сетке за день (символы | сообщения):'
                ]);
                $response->message = $message;
            } else
                $response->message = 'Такой сетки не существует!';
        } else {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && ($this->userPeer->status >= 4 || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $peers = $this->userPeer->topInfoPeer($web->id, 1);
                $message = $this->render('top/peers_list', [
                    'peers' => $peers,
                    'title' => 'Топ 15 бесед в сетке за день (символы | сообщения):'
                ]);
                $response->message = $message;
            } else
                $response->message = 'Такой сетки не существует!';
        }
        return $response;
    }

    public function peerWeekAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == intval($user_text) && $user_text != '') {
            $web = new Web(intval($user_text));
            if ($web->isExists() && ($web->owner_id == $this->user->id || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $peers = $this->userPeer->topInfoPeer($web->id, 7);
                $message = $this->render('top/peers_list', [
                    'peers' => $peers,
                    'title' => 'Топ 15 бесед в сетке за неделю (символы | сообщения):'
                ]);
                $response->message = $message;
            } else
                $response->message = 'Такой сетки не существует!';
        } else {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && ($this->userPeer->status >= 4 || ($this->user->is_dev == 1) || $web->isAdmin($this->user))) {
                $peers = $this->userPeer->topInfoPeer($web->id, 7);
                $message = $this->render('top/peers_list', [
                    'peers' => $peers,
                    'title' => 'Топ 15 бесед в сетке за неделю (символы | сообщения):'
                ]);
                $response->message = $message;
            } else
                $response->message = 'Такой сетки не существует!';
        }
        return $response;
    }

    public function webSetAdminAction($object, $user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->peer->web_id > 0) {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && $web->owner_id == $this->user->id) {
                $obj = $this->getUserFromMessage($user_text);
                if (get_class($obj) == self::$classUser) {
                    $user = $obj;
                } else {
                    $id = $this->getIdFromMessage($object);
                    if ($id > 0) {
                        $user = new User($id);
                    }
                }
                if (isset($user) && $user->isExists()) {
                    $web = new Web($this->peer->web_id);
                    $res = $web->setAdmin($user);
                    $response->message = 'Успешно!';
                } else
                    $response->message = 'неудачно';
            } else {
                $response->message = 'Вы не создатель сетки';
            }
        } else {
            $response->message = 'Сетка не привязана';
        }
        return $response;
    }

    public function webUnsetAdminAction($object, $user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->peer->web_id > 0) {
            $web = new Web($this->peer->web_id);
            if ($web->isExists() && $web->owner_id == $this->user->id) {
                $obj = $this->getUserFromMessage($user_text);
                if (get_class($obj) == self::$classUser) {
                    $user = $obj;
                } else {
                    $id = $this->getIdFromMessage($object);
                    if ($id > 0) {
                        $user = new User($id);
                    }
                }
                if (isset($user) && $user->isExists()) {
                    $web = new Web($this->peer->web_id);
                    $res = $web->unsetAdmin($user);
                    $response->message = 'Успешно!';
                } else
                    $response->message = 'неудачно';
            } else {
                $response->message = 'Вы не создатель сетки';
            }
        } else {
            $response->message = 'Сетка не привязана';
        }
        return $response;
    }

    public function WebInfoAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $web = Web::findById($this->peer->web_id);
        $list = '';
        if ($web !== false) {
            if ($web->owner_id == $this->user->id || $this->user->is_dev || $web->isAdmin($this->user)) {
                if ($user_text == '') {
                    $peers = Peer::findByWeb($this->peer->web_id);
                    $web = new Web($this->peer->web_id);
                    $list = "Актив админов из сетки {$web->name}" . PHP_EOL;
                    $number = 1;
                    foreach ($peers as $peer) {
                        $peer = new Peer($peer['id']);
                        $owner = new User($peer->owner_id);
                        $admins = UserPeer::getAdmins($peer->id);
//                        $list .= "$number) Беседа {$peer['title']} " . PHP_EOL . " Было участников: {$peer['users_count_old']} Стало: {$peer['users_count_old']}" . PHP_EOL . "Киков за вчера и сегодня: {$peer['count_kick_old']}/{$peer['count_kick']}" . PHP_EOL . "Вышли сами: хз скок, придумайте как посчитать...." . PHP_EOL;
                        $list .= $this->render('web/peer_info', [
                            'peer' => $peer,
                            'owner' => $owner,
                            'adminList' => $this->render('admin/list', [
                                'admins' => $admins,
                                'peer' => $peer
                            ])
                        ]);
                        $number++;
                        if ($number % 5 == 0) {
                            $this->vk->messagesSend($this->peer->id, $list);
                            $list = '';
                        }
                    }
                }
                $response->message = $list;
            } else {
                $response->message = "Вы не создатель данной сетки.";
            }
        }
        return $response;
    }

//    public function webTrySetAction($user_text): Response
//    {
//        $response = new Response();
//        $response->peer_id = $this->peer->id;
//        $web = Web::findByWebId($user_text);
//        if($web !== false)
//        {
//        }
//        else
//        {
//            $response->message = "Сетка не найдена!";
//        }
//        return $response;
//    }
}