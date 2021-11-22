<?php


namespace controller\control;

use comboModel\UserPeer;
use core\Controller;
use model\Clan;
use model\Web;
use core\Response;

class TopController extends Controller
{
    public function allAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $users = $this->userPeer->topInfo();
            $message = $this->render('top/list', [
                'users' => $users,
                'title' => 'Топ 20 пользователей в данной беседе за все время(символы | сообщения):'
            ]);
            $response->message = $message;
        }
        return $response;
    }

    public function HallGloryAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $clan = Clan::findAllClan();
            $user_level = UserPeer::FindLevelByChar();
            $level = UserPeer::FindLevelAll();
            $peer = UserPeer::FindTopPeer();
            $peerTopDay = UserPeer::FindTopPeerDay();
            $message = $this->render('top/hallOfGlory', [
                'title' => '🏆Зал славы Бота🏆',
                'user_top_level' => $user_level,
                'top_clan_glory' => $clan,
                'level' => $level,
                'top_peer' => $peer,
                'day_top_peer' => $peerTopDay
            ]);
            $response->message = $message;
        }
        return $response;
    }

    public function LevelUsersAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $users = $this->userPeer->FindLevel($this->peer->id);
            $message = $this->render('top/levels', [
                'users' => $users,
                'title' => 'Топ 20 пользователей в данной беседе по уровню:'
            ]);
            $response->message = $message;
        }
        return $response;
    }

    public function dayAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $users = $this->userPeer->topInfo(1);
            $message = $this->render('top/list', [
                'users' => $users,
                'title' => 'Топ 20 пользователей в данной беседе за день(символы | сообщения):'
            ]);
            $response->message = $message;
        }
        return $response;
    }

    public function weekAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $users = $this->userPeer->topInfo(7);
            $message = $this->render('top/list', [
                'users' => $users,
                'title' => 'Топ 20 пользователей на этой неделе(символы | сообщения):'
            ]);
            $response->message = $message;
        }
        return $response;
    }
}