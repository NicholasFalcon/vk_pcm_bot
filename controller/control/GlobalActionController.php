<?php


namespace controller\control;

use comboModel\UserPeer;
use core\Controller;
use model\Group;
use model\Role;
use model\Web;
use core\Response;
use model\User;
use core\App;

class GlobalActionController extends Controller
{
    public function inviteUserAction($object): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if (isset($object['message']['action']) && ($object['message']['action']['type'] == 'chat_invite_user' || $object['message']['action']['type'] == 'chat_invite_user_by_link')) {
            $user_id = 0;
            if($object['message']['action']['type'] == 'chat_invite_user')
                $user_id = $object['message']['action']['member_id'];
            elseif($object['message']['action']['type'] == 'chat_invite_user_by_link')
                $user_id = $object['message']['from_id'];
            if ($user_id > 0) {
                $user = User::findById($user_id);
                if ($user === false) {
                    $user = new User();
                    $user->id = $user_id;
                    $user_data = App::getFullInfoAboutUser($user_id);
                    $user->updateInfo($user_data);
                }
                $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
                if ($userPeer === false) {
                    $userPeer = new UserPeer();
                    $userPeer->peer_id = $this->peer->id;
                    $userPeer->user_id = $user->id;
                }
                $userPeer->role_id = Role::USER;
                $userPeer->last_tst = time();
                if($userPeer->reg_tst == 0)
                    $userPeer->reg_tst = time();
                $userPeer->deleted = 0;
                $userPeer->save();
                $this->peer->users_count = $this->peer->users_count + 1;
                $this->peer->save();
                if ($userPeer->have_ban == 1) {
                    $userPeer->deleted = 1;
                    $userPeer->save();
                    $this->peer->users_count = $this->peer->users_count - 1;
                    $this->peer->save();
                    $result = $this->vk->messagesRemoveChatUser($this->peer->id, $userPeer->user_id);
                    if (isset($result['response']) && $result['response'] == 1) {
                        $response->message = 'Приглашен забаненный пользователь, исключаю....';
                        $response->setButton("-бан {$userPeer->user_id}", 'remove_ban');
                    }
                }
                $web = new Web($this->peer->web_id);
                if ($web->isExists() && $web->haveBan($user->id)) {
                    $userPeer->deleted = 1;
                    $userPeer->save();
                    $this->peer->users_count = $this->peer->users_count - 1;
                    $this->peer->save();
                    $result = $this->vk->messagesRemoveChatUser($this->peer->id, $userPeer->user_id);
                    if (isset($result['response']) && $result['response'] == 1) {
                        $response->message = 'Приглашен глобально забаненный пользователь, исключаю....';
                        $response->setButton("-глобан {$userPeer->user_id}", 'remove_globan');
                    }
                }
                $res = $this->userPeer->getCallback('sendHelloMessage', false);
                if($res != 'sendHelloMessage')
                    $this->userPeer->createCallback('sendHelloMessage', time());
            } elseif ($user_id < 0 && $user_id != -App::$group_id) {
                $group = Group::findById($user_id);
                if ($group == false) {
                    $group = new Group();
                    $group->id = $user_id;
                    $groupData = $this->vk->groupsGetById(abs($user_id));
                    $groupData = $groupData['response'][0];
                    $group->name = $groupData['name'];
                    $group->domain = $groupData['screen_name'];
                    $group->save();
                    $groupPeer = $group->findByPeer($this->peer->id);
                    if ($groupPeer == []) {
                        $group->createPeer($this->peer->id);
                    }
                    if ($this->peer->getSetting(App::S_AUTOKICK_GROUPS) == 1)
                        $this->vk->messagesRemoveChatUser($this->peer->id, $user_id);
                } else {
                    if ($group->haveBan($this->peer->id)) {
                        $result = $this->vk->messagesRemoveChatUser($this->peer->id, $group->id);
                        if (isset($result['response']) && $result['response'] == 1) {
                            $response->message = 'Приглашена забаненная группа, исключаю....';
                            $response->setButton("-бан {$group->id}", 'remove_ban');
                        }
                    } elseif ($group->have_ban == 1) {
                        $result = $this->vk->messagesRemoveChatUser($this->peer->id, $group->id);
                        if (isset($result['response']) && $result['response'] == 1) {
                            $response->message = 'Приглашена глобально забаненная группа по причине:' . PHP_EOL
                                . $group->message . PHP_EOL
                                . 'исключаю...';
                        }
                    }
                    if ($this->peer->getSetting(App::S_AUTOKICK_GROUPS) == 1)
                        $this->vk->messagesRemoveChatUser($this->peer->id, $group->id);
                    $group->unsetDeleted($this->peer->id);
                }
            } else {
                $message = $this->render('main/init');
                $response->message = $message;
                $response->setButton('беседа инициализация', 'peer_init');
            }
        }
        return $response;
    }

    public function leaveUserAction($object): Response
    {
        var_dump($object);
        if (isset($object['message']['action']) && $object['message']['action']['type'] == 'chat_kick_user') {
            if ($object['message']['action']['member_id'] > 0) {
                $user_id = $object['message']['action']['member_id'];
                $user = User::findById($user_id);
                if($user !== false)
                {
                    $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
                    if($userPeer !== false)
                    {
                        $userPeer->role_id = Role::USER;
                        if($this->user->id == $user->id)
                        {
                            if ($this->peer->getSetting(App::S_KICK_LIVERS) == 1) {
                                $this->vk->messagesRemoveChatUser($this->peer->id, $user->id);
                                $userPeer->deleted = 1;
                            }
                        }
                        else
                        {
                            $userPeer->deleted = 1;
                        }
                        $this->peer->users_count = $this->peer->users_count - 1;
                        $this->peer->save();
                    }
                    $userPeer->save();
                }
            } elseif ($object['message']['action']['member_id'] < 0) {
                $group = Group::findById($object['message']['action']['member_id']);
                $group->setDeleted($this->peer->id);
            }
        }
        return new Response();
    }

    public function helpAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '')
            $response->message = "Весь список команд можно посмотреть в статье по ссылке:"
                . PHP_EOL . "vk.com/@pcm_bot-komandy-bota"
                . PHP_EOL . "Если есть идеи по контенту бота пишите [hironori|Николя]";
        return $response;
    }
}