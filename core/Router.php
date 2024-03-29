<?php

namespace core;

use api\Vk;
use controller\control\CoreController;
use controller\user\VariablesController;
use model\Role;
use model\User;
use model\Peer;
use model\Sysinfo;
use comboModel\UserPeer;
use controller\control\TriggerController;
use controller\control\CallbackController;
use model\Web;
use Swoole\Process;

class Router
{
    private Vk $vk;
    private array $processes = [];
    public int $timeStart = 0;
    private Routing $routing_peer;
    private Routing $routing_user;
    private Routing $commands_peer;
    private Routing $commands_user;

    public function __construct($config)
    {
        $this->vk = new Vk($config->token, $config->version, $config->domain, $config->id);
        App::$group_id = $config->id;
        $this->routing_peer = new Routing(Routing::PEER);
        $this->commands_peer = new Routing(Routing::C_PEER);
        $this->routing_user = new Routing(Routing::USER);
        $this->commands_user = new Routing(Routing::C_USER);
    }

    public function start()
    {
        $longPollServer = $this->vk->groupsGetLongPollServer();
        file_put_contents('config/ts.data', $longPollServer['response']['ts']);
        echo 'Started!'.PHP_EOL;
        echo 'Стартанул!'.PHP_EOL;
        $success = true;
        while ($success)
            if(!$this->check($longPollServer))
            {
                $success = false;
            }
        $this->start();
    }

    public function check($longPollServer)
    {
        $ts = file_get_contents('config/ts.data');
        $dataLongPoll = $this->vk->checkUpdateLongPollServer($longPollServer['response']['server'], $longPollServer['response']['key'], $ts);
        if(!isset($dataLongPoll['ts']))
        {
            file_put_contents('longpoll.log', 'longpoll server error: '.print_r($dataLongPoll, 1).PHP_EOL, FILE_APPEND);
            return false;
        }
        file_put_contents('config/ts.data', $dataLongPoll['ts']);
        foreach ($dataLongPoll['updates'] as $action)
        {

            $router = $this;

            $process = new Process(function (Process $process) use ($router, $action) {
                try {
                    $router->createAction($action);
                }
                catch (\Exception $e)
                {
                    echo $e->getMessage();
                } finally {
                    $process->exit();
                }
            });

            $process->start();
        }

        return true;
    }

    public function createAction($data)
    {
        $this->timeStart = time()+microtime(1);
        $action = $data;
        $response = $this->route($action);
        $response->useAction($this->vk);
    }

    public function route($action)
    {
        if(intval($action['object']['message']['peer_id']) > App::$peerStartNumber) {
            $peer = Peer::findById($action['object']['message']['peer_id']);
            if ($peer == false) {
                $peer = new Peer();
                $peer->id = $action['object']['message']['peer_id'];
                $peer->save();
            }
            if ($action['type'] == 'message_new' && intval($action['object']['message']['from_id']) > 0) {
                $user = User::findById($action['object']['message']['from_id']);
                if ($user == false) {
                    $user = new User();
                    $user->id = $action['object']['message']['from_id'];
                    $userInfo = App::getFullInfoAboutUser($user->id);
                    $user->updateInfo($userInfo);
                }
                if ($user->black_list == 0) {
                    $userPeer = UserPeer::findsByPeerAndUser($user->id, $peer->id);
                    if ($userPeer == false) {
                        $userPeer = new UserPeer();
                        $userPeer->peer_id = $peer->id;
                        $userPeer->user_id = $user->id;
                        $userPeer->have_ban = 0;
                        $userPeer->role_id = Role::USER;
                        $userPeer->reg_tst = time();
                    } else {
                        $userPeer->deleted = 0;
                    }
                    $number = 1;
                    $level = 0;
                    if ($userPeer->char_all >= 2000)
                    {
                        while ($level != 1) {
                            $level = floor($userPeer->char_all / (pow(2, $number) * 1000));
                            $number++;
                        }
                    }
                    else {
                        $level = 0;
                    }
                    if ($number - $level != $userPeer->level) {
                        $userPeer->level = $number - $level;
                    }

                    $userPeer->char_day = $userPeer->char_day + mb_strlen($action['object']['message']['text']);
                    $userPeer->char_week = $userPeer->char_week + mb_strlen($action['object']['message']['text']);
                    $userPeer->char_all = $userPeer->char_all + mb_strlen($action['object']['message']['text']);

                    $userPeer->msg_day = $userPeer->msg_day + 1;
                    $userPeer->msg_week = $userPeer->msg_week + 1;
                    $userPeer->msg_all = $userPeer->msg_all + 1;
                    $userPeer->last_tst = time();

                    $route = false;
                    if (isset($action['object']['message']['payload']) && $action['object']['message']['payload'] != '')
                    {
                        $payload = json_decode($action['object']['message']['payload'], 'true');
                        $name = '';
                        if(isset($payload['pcmButtonAction']))
                            $name = $payload['pcmButtonAction'];
                        elseif (isset($payload['command']))
                            $name = $payload['command'];
                        $route = $this->commands_peer->check($name);
                    }
                    if ($route === false && isset($action['object']['message']['action']) && isset($action['object']['message']['action']['type']) && $action['object']['message']['action']['type'] != '')
                    {
                        $route = $this->commands_peer->check($action['object']['message']['action']['type']);
                    }
                    elseif($route === false && $action['object']['message']['text'] != '') {
                        $route = $this->routing_peer->check($action['object']['message']['text']);
                    }
                    $userPeer->save();
                    if ($peer->MutePeer == 1) {
                        if (!$userPeer->haveAccess(Role::MUTE_ACCESS) && $user->is_dev == 0) {
                            $userPeer->deleted = 1;
                            $userPeer->muted = 0;
                            $userPeer->role_id = Role::USER;
                            $userPeer->save();
                            $this->vk->messagesSend($peer->id, "Человек написал сообщение во время тихого часа, удаляю его...");
                            $this->vk->messagesRemoveChatUser($peer->id, $userPeer->user_id);
                        }
                    }
                    if ($userPeer->muted >= time()) {
                        $userPeer->deleted = 1;
                        $userPeer->muted = 0;
                        $userPeer->role_id = Role::USER;
                        $userPeer->save();
                        $this->vk->messagesSend($peer->id, "Человек написал сообщение, удаляю его....");
                        //$this->vk->messagesSend($peer->id, print_r($action['object']['message'],1));
//                    $this->vk->messagesDelete($action['object']['message']['conversation_message_id']);
                        $this->vk->messagesRemoveChatUser($peer->id, $userPeer->user_id);
                    }
                    $web = new Web($peer->web_id);
                    if ($web->isExists() && $web->haveBan($user->id)) {
                        $userPeer->deleted = 1;
                        $userPeer->role_id = Role::USER;
                        $userPeer->save();
                        $peer->users_count = $peer->users_count - 1;
                        $peer->save();
                        $result = $this->vk->messagesRemoveChatUser($peer->id, $userPeer->user_id);
                        if (isset($result['response']) && $result['response'] == 1) {
                            $this->vk->messagesSend($peer->id, "Сообщение написал глобально забанненый пользователь, удаляю....");
                        }
                    }
                    /**
                     * @var Controller $controller
                     */
                    if ($route !== false) {
                        if(isset($route['error']))
                        {
                            if($route['error'] == 'validation')
                            {
                                $response = new Response();
                                $response->message = $route['msg'];
                                $response->peer_id = $peer->id;
                                return $response;
                            }
                            else
                            {
                                return new Response();
                            }
                        }
                        $class = $route['class'];
                        if (class_exists($class)) {
                            $controller = new $class($this->vk, $user, $peer, $userPeer);
                            if(!$controller::$isGlobal && $peer->init == 0)
                            {
                                $response = new Response();
                                $response->peer_id = $peer->id;
                                $response->message = 'Для использования полного функционала бота проведите инициализацию беседы (беседа инициализация)';
                                $response->setButton('беседа инициализация', 'peer_init');
                                return $response;
                            }
                            return $controller->run($route['action'], array_merge(['time_start' => $this->timeStart], $action, $route['params']));
                        }
                    } elseif (!$peer->getSetting(App::S_USE_TRIGGERS)) {
                        $controller = new TriggerController($this->vk, $user, $peer, $userPeer);
                        return $controller->getAction($action['object']);
                    }
                }
            }
        }
        else
        {
            if ($action['type'] == 'message_new' && intval($action['object']['message']['from_id']) > 0) {
                $user = User::findById($action['object']['message']['from_id']);
                if ($user == false) {
                    $user = new User();
                    $user->id = $action['object']['message']['from_id'];
                    $userInfo = App::getFullInfoAboutUser($user->id);
                    $user->updateInfo($userInfo);
                }
                $route = false;
                if (isset($action['object']['message']['payload']) && $action['object']['message']['payload'] != '')
                {
                    $payload = json_decode($action['object']['message']['payload'], 'true');
                    $name = '';
                    if(isset($payload['pcmButtonAction']))
                        $name = $payload['pcmButtonAction'];
                    elseif (isset($payload['command']))
                        $name = $payload['command'];
                    $route = $this->commands_user->check($name);
                }
                elseif ($action['object']['message']['text'] != '') {
                    $route = $this->routing_user->check($action['object']['message']['text']);
                }
                if($user->user_action != '')
                {
                    $data = explode(' ', $user->user_action);
                    $action_name = $data[0].'Action';
                    unset($data[0]);
                    $variables = [];
                    if(is_array($data))
                    {
                        $variables = $data;
                    }
                    $controller = new VariablesController($this->vk, $user, new Peer(), new UserPeer());
                    $response = $controller->run($action_name, array_merge(["user_text" => $action['object']['message']['text'] ?? '', "variables" => $variables, 'time_start' => $this->timeStart], $action));
                    $user->user_action = '';
                    $user->save();
                    if($response != false)
                    {
                        return $response;
                    }
                }
                if ($route !== false) {
                    $class = $route['class'];
                    if (class_exists($class)) {
                        $controller = new $class($this->vk, $user, new Peer(), new UserPeer());
                        return $controller->run($route['action'], array_merge(['time_start' => $this->timeStart], $action, $route['params']));
                    }
                }
            }
        }
        return new Response();
    }

    public function getAction($command, $configName = 'action')
    {
        $id = App::$group_id;
        $command = trim(preg_replace("~\[club$id\|.*]~", '', $command));
        $actions = json_decode(file_get_contents("config/$configName.json"), true);
        $commands = explode(' ', $command);
        foreach ($commands as $key => $one)
        {
            $one = mb_strtolower($one);
            if(isset($actions[$one]))
            {
                $actions = $actions[$one];
                if(isset($actions['action']) && $actions['action'] != '')
                {
                    $actions['user_text'] = implode(' ', array_splice($commands, $key+1, count($commands)-1));
                    return $actions;
                }
            }
            elseif(isset($actions['*']))
            {
                $actions = $actions['*'];
                if(isset($actions['action']) && $actions['action'] != '')
                {
                    $actions['user_text'] = implode(' ', array_splice($commands, $key, count($commands)-1));
                    return $actions;
                }
            }
            else
            {
                return false;
            }
        }
        if(isset($actions['']))
        {
            $actions = $actions[''];
            if(isset($actions['action']) && $actions['action'] != '')
            {
                return $actions;
            }
        }
        return false;
    }

    public function sendNotification()
    {
        $time = time();
        $notifications = App::getPBase()
            ->select()
            ->from('notification')
            ->where("`tst` < $time")
            ->query();
        App::getPBase()
            ->delete('notification')
            ->where("`tst` < $time")
            ->run();
        foreach ($notifications as $notification)
        {
            $this->vk->messagesSend($notification['peer_id'], $notification['message']);
        }
    }

    public function runCallback()
    {
        $time = time();
        $callbacks = App::getPBase()
            ->select()
            ->from('callback')
            ->where("`tst` < $time")
            ->query();
        App::getPBase()
            ->delete('callback')
            ->where("`tst` < $time")
            ->run();
        foreach ($callbacks as $callback)
        {
            $user = new User($callback['user_id']);
            $peer = new Peer($callback['peer_id']);
            if($user->isExists() && $peer->isExists()) {
                $userPeer = UserPeer::findsByPeerAndUser($user->id, $peer->id);
                $controller = new CallbackController($this->vk, $user, $peer, $userPeer);
                $response = $controller->run($callback['action'].'Action', json_decode($callback['params'], true));
                $response->useAction($this->vk);
            }
        }
    }

    public function runDaily()
    {
        $info = new Sysinfo(1);
        $curDay = date('N', time());
        UserPeer::resetDay();
        if($info->day_id > $curDay)
            UserPeer::resetWeek();
        $info->day_id = $curDay;
        $info->save();
        $peers = Peer::findAllAutokick();
        foreach ($peers as $peer) {
            $this->vk->messagesSend($peer['id'], 'Запущена чистка беседы от неактива!');
            $users = UserPeer::selectAllNeedKick($peer['id']);
            foreach ($users as $user) {
                $userPeer = UserPeer::findsByPeerAndUser($user['user_id'], $peer['id']);
                $userPeer->deleted = 1;
                $userPeer->save();
                $this->vk->messagesRemoveChatUser($peer['id'], $user['user_id']);
            }
            $this->vk->messagesSend($peer['id'], 'Чистка окончена!');
        }
        $peers = Peer::findAll();
        foreach ($peers as $peer) {
            $chat = new Peer($peer['id']);
            $users = $this->vk->messagesGetConversationMembers($peer['id']);
            $chat->users_count_old = $chat->users_count;
            $chat->count_kick_old = $chat->count_kick;
            $chat->count_kick = 0;
            $chat->users_count = count($users['response']['items']);
            $chat->save();
            $users = UserPeer::SelectAll($peer['id']);
            foreach ($users as $user) {
                $userPeer = UserPeer::findsByPeerAndUser($user['user_id'], $peer['id']);
                $userPeer->check = 0;
                $userPeer->save();
            }
        }
        $this->vk->messagesSend(App::$mainPeer, 'Информация обновлена, чистки проведены');
    }
}