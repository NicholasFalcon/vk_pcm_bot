<?php

namespace core;

use comboModel\UserPeer;
use model\Group;
use model\Peer;
use model\Role;
use model\User;
use modules\PhpBase;
use api\Vk;

class App
{
    static PhpBase $pBase;
    static Vk $vk;
    static int $mainPeer = 2000000001;
    static int $peerStartNumber = 2000000000;
    static int $group_id = 0;

    public const S_KICK_LIVERS = 1;
    public const S_USE_TRIGGERS = 2;
    public const S_COUNT_WARNS = 3;
    public const S_INACTIVE_KICK_TIME = 4;
    public const S_INACTIVE_KICK = 5;
    public const S_KICK_URL = 6;
    public const S_KICK_INVITE_URL = 7;
    public const S_KICK_SPAM = 8;
    public const S_AUTOKICK_GROUPS = 9;
    public const S_COUNT_ATTENTION = 10;
    public const S_KICK_GROUPS = 11;
    public const S_COLOR_BUTTON = 12;
    public const S_HELLO_MESSAGE_SEND = 13;
    public const S_RULES_SEND = 14;
    public const S_MESSAGE_PEER = 15;

    public static function init()
    {
        $dbConfig = json_decode(file_get_contents('config/db.json'), true);
        $config = file_get_contents('config/api.json');
        $config = json_decode($config)->pcm_bot_test;
        self::$pBase = new PhpBase($dbConfig['type'], $dbConfig['user'], $dbConfig['password'], $dbConfig['db'], $dbConfig['host']);
        self::$vk = new Vk($config->token, $config->version, $config->domain, $config->id);
    }

    public static function getPBase() : PhpBase
    {
        return self::$pBase;
    }

    public static function replaceSpecialChars($str)
    {
        $str = str_replace("'", "&#039;", $str);
        $str = str_replace('"', '&#039;', $str);
        $str = str_replace('<', '&lt;', $str);
        return str_replace('>', '&gt;', $str);
    }

    public static function recoverJson($str)
    {
        $str = str_replace('&#039;', '"', $str);
        $str = str_replace('&lt;', '<', $str);
        return str_replace('&gt;', '>', $str);
    }

    public static function getFullInfoAboutUser($user_id): array
    {
        $result = [];
        $user_data = self::$vk->usersGet($user_id);
        if(isset($user_data['response']) && is_array($user_data['response']))
        {
            $user_data = $user_data['response'][0];
            $result['first_name_nom'] = $user_data['first_name'];
            $result['last_name_nom'] = $user_data['last_name'];
            $result['sex'] = $user_data['sex'];
            $result['domain'] = $user_data['domain'];
            $user_data = self::$vk->usersGet($user_id, 'gen');
            $user_data = $user_data['response'][0];
            $result['first_name_gen'] = $user_data['first_name']??'';
            $result['last_name_gen'] = $user_data['last_name']??'';
            $user_data = self::$vk->usersGet($user_id, 'dat');
            $user_data = $user_data['response'][0];
            $result['first_name_dat'] = $user_data['first_name']??'';
            $result['last_name_dat'] = $user_data['last_name']??'';
            $user_data = self::$vk->usersGet($user_id, 'acc');
            $user_data = $user_data['response'][0];
            $result['first_name_acc'] = $user_data['first_name']??'';
            $result['last_name_acc'] = $user_data['last_name']??'';
            $user_data = self::$vk->usersGet($user_id, 'ins');
            $user_data = $user_data['response'][0];
            $result['first_name_ins'] = $user_data['first_name']??'';
            $result['last_name_ins'] = $user_data['last_name']??'';
            $user_data = self::$vk->usersGet($user_id, 'abl');
            $user_data = $user_data['response'][0];
            $result['first_name_abl'] = $user_data['first_name']??'';
            $result['last_name_abl'] = $user_data['last_name']??'';
        }
        return $result;
    }

    public static function updateUsers($peer)
    {
        $users = self::$vk->messagesGetConversationMembers($peer->id)['response']['items'];
        $curUsers = UserPeer::getUsersActive($peer->id);
        $curGroups = Group::getGroupsActive($peer->id);
        foreach ($users as $user) {
            if ($user['member_id'] > 0) {
                $newUser = User::findById($user['member_id']);
                if ($newUser == false) {
                    $newUser = new User();
                    $newUser->id = $user['member_id'];
                    $newUser->save();
                }
                $user_data = App::getFullInfoAboutUser($newUser->id);
                $newUser->updateInfo($user_data);
                $userPeer = UserPeer::findsByPeerAndUser($user['member_id'], $peer->id);
                if ($userPeer == false) {
                    $userPeer = new UserPeer();
                    $userPeer->peer_id = $peer->id;
                    $userPeer->user_id = $user['member_id'];
                    $userPeer->have_ban = 0;
                    $userPeer->role_id = Role::USER;
                }
                $userPeer->deleted = 0;
                $userPeer->reg_tst = $user['join_date'];
                if($userPeer->last_tst == 0)
                    $userPeer->last_tst = time();
                if (isset($user['is_admin']) && $user['is_admin'] == true) {
                    $userPeer->role_id = Role::MAIN_ADMIN;
                } else {
                    if ($userPeer->role_id == Role::MAIN_ADMIN)
                        $userPeer->role_id = Role::USER;
                }
                $userPeer->save();
                foreach ($curUsers as $key => $cur) {
                    if ($cur['user_id'] == $userPeer->user_id)
                    {
                        unset($curUsers[$key]);
                    }
                }
            } else {
                $newGroup = Group::findById($user['member_id']);
                if ($newGroup == false) {
                    $newGroup = new Group();
                    $newGroup->id = $user['member_id'];
                    $groupData = self::$vk->groupsGetById(abs($user['member_id']));
                    $groupData = $groupData['response']['groups'][0];
                    $newGroup->name = $groupData['name'];
                    $newGroup->domain = $groupData['screen_name'];
                    $newGroup->save();
                }
                $groupPeer = $newGroup->findByPeer($peer->id);
                if ($groupPeer == []) {
                    $newGroup->createPeer($peer->id);
                }
                if (isset($user['is_admin']) && $user['is_admin'] == true) {
                    $newGroup->setAdmin($peer->id);
                }
                foreach ($curGroups as $key => $cur) {
                    if ($cur['group_id'] == $user['member_id'])
                        unset($curGroups[$key]);
                }
            }
        }
        foreach ($curUsers as $user) {
            $obj = UserPeer::findsByPeerAndUser($user['user_id'], $peer->id);
            $obj->deleted = 1;
            $obj->save();
        }
        foreach ($curGroups as $group) {
            $group = new Group($group['group_id']);
            if ($group->isExists())
                $group->setDeleted($peer->id);
        }
    }

    public static function updatePeer($peer_id)
    {
        $peer_data = self::$vk->messagesGetConversationsById($peer_id);
        $peer = new Peer($peer_id);
        if(isset($peer_data['response']) && is_array($peer_data['response']))
        {
            $peer->title = $peer_data['response']['items'][0]['chat_settings']['title'];
            $peer->owner_id = $peer_data['response']['items'][0]['chat_settings']['owner_id'];
            $peer->users_count = $peer_data['response']['items'][0]['chat_settings']['members_count'];
        }
        return $peer->save();
    }
}