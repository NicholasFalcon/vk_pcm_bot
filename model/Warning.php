<?php


namespace model;

use comboModel\UserPeer;
use core\Model;
use core\App;

/**
 * Class Warning
 * @package model
 * @property int $user_id
 * @property int $peer_id
 * @property int $tst
 */
class Warning extends Model
{
    public static string $table = 'warnings';
    public int $main_id = 0;

    public static function GetWarningId($user_id, $peer_id)
    {
        $data = App::getPBase()
            ->select('id')
            ->from(static::$table)
            ->where("`user_id` = '$user_id' and `peer_id` = '$peer_id'")
            ->queryOne('id');
        if ($data > 0)
        {
            $warn = new Warning($data);
            $warn->main_id = $user_id;
            return $warn;
        }
        return false;
    }

    public static function getWarnings(UserPeer $userPeer)
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`user_id`=$userPeer->user_id and `peer_id`=$userPeer->peer_id")
            ->queryOne('count');
    }

    public static function clear(UserPeer $userPeer)
    {
        return App::getPBase()
            ->delete(static::$table)
            ->where("`user_id`=$userPeer->user_id and `peer_id`=$userPeer->peer_id")
            ->run();
    }

    public static function getAllWarning ($peer_id)
    {
        $users = App::getPBase()
            ->select( ['users.id', 'id'], 'first_name_nom', 'last_name_nom', ['count(*)', 'count'])
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->where("`peer_id` = '$peer_id'")
            ->groupBy('users.id', 'first_name_nom', 'last_name_nom')
            ->orderBy(['count(*)', 'desc'])
            ->query();
        foreach ($users as $key => $user)
        {
            $userPeer = UserPeer::findsByPeerAndUser($user['id'], $peer_id);
            if($userPeer->deleted == 1)
                unset($users[$key]);
        }
        return $users;
    }
}