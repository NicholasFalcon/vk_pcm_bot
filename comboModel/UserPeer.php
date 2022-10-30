<?php


namespace comboModel;

use core\ComboModel;
use core\App;
use model\Peer;
use model\Role;

/**
 * Class UserPeer
 * @package comboModel
 * @property int $peer_id
 * @property int $user_id
 * @property int have_ban
 * @property int role_id
 * @property int msg_day
 * @property int char_day
 * @property int msg_week
 * @property int char_week
 * @property int msg_all
 * @property int char_all
 * @property int last_tst
 * @property int reg_tst
 * @property int deleted
 * @property int muted
 * @property int check
 * @property int level
 * @property int kick_by_peer
 * @property int ban_by_peer
 * @property int leave
 */
class UserPeer extends ComboModel
{
    static string $table = 'users_peer_info';

    public static function findsByPeerAndUser($user_id, $peer_id)
    {
        $data = parent::findsBy([
            'user_id' => $user_id,
            'peer_id' => $peer_id
        ]);
        if($data != [])
        {
            return new UserPeer([
                'peer_id' => $data['peer_id'],
                'user_id' => $data['user_id']
            ]);
        }
        return false;
    }

    public static function findAllByPeerAndRole($peer_id, $role_id)
    {
        return parent::findAllBy([
            'role_id' => $role_id,
            'peer_id' => $peer_id
        ]);
    }

    public static function SelectAll($peer_id)
    {
        return App::getPBase()
            ->select('peer_id', 'user_id')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->query();
    }

    public static function SelectUsers($peer_id)
    {
        return App::getPBase()
            ->select('peer_id', 'user_id')
            ->from(static::$table)
            ->where("`peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->query();
    }

    public static function getAdmins($peer_id)
    {
        return App::getPBase()
            ->select('first_name_nom', 'last_name_nom', 'id')
            ->from('users')
            ->innerJoin('users', 'id', 'users_peer_info', 'user_id')
            ->where("`role_id` = '".Role::MAIN_ADMIN."' and `peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->query();
    }
    
    public static function resetDay()
    {
        return App::getPBase()
            ->update(static::$table)
            ->set('msg_day', 0)
            ->set('char_day', 0)
            ->run();
    }

    public static function resetWeek()
    {
        return App::getPBase()
            ->update(static::$table)
            ->set('msg_week', 0)
            ->set('char_week', 0)
            ->run();
    }

    public function topInfo($time = 0)
    {
        if($time == 1)
            return App::getPBase()
                ->select('first_name_nom', 'last_name_nom', ['msg_day', 'msg'], ['char_day', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'user_id', 'users', 'id')
                ->where("`char_day` > 0 and `peer_id` = $this->peer_id and `deleted` = 0")
                ->orderBy(['char_day', 'desc'])
                ->limit(20)
                ->query();
        elseif ($time == 7)
            return App::getPBase()
                ->select('first_name_nom', 'last_name_nom', ['msg_week', 'msg'], ['char_week', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'user_id', 'users', 'id')
                ->where("`char_week` > 0 and `peer_id` = $this->peer_id  and `deleted` = 0")
                ->orderBy(['char_week', 'desc'])
                ->limit(20)
                ->query();
        else
            return App::getPBase()
                ->select('first_name_nom', 'last_name_nom', ['msg_all', 'msg'], ['char_all', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'user_id', 'users', 'id')
                ->where("`char_all` > 0 and `peer_id` = $this->peer_id and `deleted` = 0")
                ->orderBy(['char_all', 'desc'])
                ->limit(20)
                ->query();
    }

    public function topInfoWeb($web_id, $time = 0)
    {
        if($time == 1)
            return App::getPBase()
                ->select(['users.`id`'], 'first_name_nom', 'last_name_nom', ['sum(msg_day)', 'msg'], ['sum(char_day)', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'user_id', 'users', 'id')
                ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
                ->where("`char_day` > 0 and `web_id` = '$web_id'")
                ->groupBy('users.id', 'first_name_nom', 'last_name_nom')
                ->orderBy(['sum(char_day)', 'desc'])
                ->limit(20)
                ->query();
        elseif ($time == 7)
            return App::getPBase()
                ->select(['users.`id`'], 'first_name_nom', 'last_name_nom', ['sum(msg_week)', 'msg'], ['sum(char_week)', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'user_id', 'users', 'id')
                ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
                ->where("`char_week` > 0 and `web_id` = '$web_id'")
                ->groupBy('users.id', 'first_name_nom', 'last_name_nom')
                ->orderBy(['sum(char_week)', 'desc'])
                ->limit(20)
                ->query();
        else
            return App::getPBase()
                ->select(['users.`id`'], 'first_name_nom', 'last_name_nom', ['sum(msg_all)', 'msg'], ['sum(char_all)', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'user_id', 'users', 'id')
                ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
                ->where("`char_all` > 0 and `web_id` = '$web_id'")
                ->groupBy('users.id', 'first_name_nom', 'last_name_nom')
                ->orderBy(['sum(char_all)', 'desc'])
                ->limit(20)
                ->query();
    }

    public function topInfoPeer($web_id, $time = 0)
    {
        if($time == 1)
            return App::getPBase()
                ->select(['peers.`id`'], 'title', ['sum(msg_day)', 'msg'], ['sum(char_day)', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
                ->where("`web_id` = $web_id")
                ->groupBy('peers.id', 'title')
                ->orderBy(['sum(char_day)', 'desc'])
                ->limit(15)
                ->query();
        elseif ($time == 7)
            return App::getPBase()->select(['peers.`id`'], 'title', ['sum(msg_week)', 'msg'], ['sum(char_week)', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
                ->where("`web_id` = $web_id")
                ->groupBy('peers.id', 'title')
                ->orderBy(['sum(char_week)', 'desc'])
                ->limit(15)
                ->query();
        else
            return App::getPBase()->select(['peers.`id`'], 'title', ['sum(msg_all)', 'msg'], ['sum(char_all)', 'length'])
                ->from(static::$table)
                ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
                ->where("`web_id` = $web_id")
                ->groupBy('peers.id', 'title')
                ->orderBy(['sum(char_all)', 'desc'])
                ->limit(15)
                ->query();
    }

    public function getRandomUserBySex(): array
    {
        $data = [];
        $data2 = [];
        array_push($data, App::getPBase()
            ->select('user_id')
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->where("`peer_id` = $this->peer_id and `user_id` > 0 and `deleted` = 0 and `sex` = 2")
            ->query());
        array_push($data2, App::getPBase()
            ->select('user_id')
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->where("`peer_id` = $this->peer_id and `user_id` > 0 and `deleted` = 0 and `sex` = 1")
            ->query());
        return array($data, $data2);
    }

    public function getRandomUser()
    {
        return App::getPBase()
            ->select('user_id')
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->where("`peer_id` = $this->peer_id and `user_id` > 0 and `deleted` = 0")
            ->query();
    }

    public static function findByPeer($peer_id)
    {
        return App::getPBase()
            ->select('user_id')
            ->from(static::$table)
            ->where("`peer_id`  = $peer_id")
            ->query();
    }

    public function getRegDay(): int
    {
        return intval((time() - $this->reg_tst) / (3600*24));
    }

    public static function selectAllNeedKick($peer_id)
    {
        $peer = new Peer($peer_id);
        $time = (time() - ($peer->getSetting(8)*3600*24));
        return App::getPBase()
            ->select('peer_id', 'user_id', 'last_tst')
            ->from(static::$table)
            ->where("`last_tst` < $time and `peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->orderBy(['peer_id', 'asc'])
            ->query();
    }

    public static function SelectNeedKick($time, $peer_id)
    {
        $timer = (time() - ($time*3600*24));
        return App::getPBase()
            ->select('peer_id', 'user_id', 'last_tst')
            ->from(static::$table)
            ->where("`last_tst` < $timer and `peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->query();
    }

    public static function SelectIsBan($peer_id)
    {
        return App::getPBase()
            ->select('user_id')
            ->from(static::$table)
            ->where("`have_ban` = 1 and `peer_id` = '$peer_id' and `user_id` > 0")
            ->limit(50)
            ->query();
    }

    public static function findInactive($peer_id)
    {
        $time = (time() - 3600);
        return App::getPBase()
            ->select('peer_id', 'user_id', 'last_tst', 'deleted')
            ->from(static::$table)
            ->where("`last_tst` < $time and `peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->orderBy(['last_tst', 'desc'])
            ->query();
    }

    public static function getSleepersUsers($peer_id)
    {
        $peer = new Peer($peer_id);
        $time = (time() - ($peer->getSetting(8)*3600*24));
        return App::getPBase()
            ->select('user_id', 'last_tst')
            ->from(static::$table)
            ->where("`last_tst` < '$time' and `peer_id` = '$peer_id' and `user_id` > 0 and `deleted` = 0")
            ->query();
    }

    public static function getUsersActive($peer_id)
    {
        return App::getPBase()
            ->select('user_id')
            ->from(static::$table)
            ->where("`peer_id` = $peer_id and `deleted` = 0")
            ->query();
    }

    public static function FindLevelByChar()
    {
        return App::getPBase()
            ->select('first_name_nom', 'last_name_nom', ['sum(msg_all)', 'msg'], ['sum(char_all)', 'length'], ['level'], ['user_id'])
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
            ->where("`char_all` > 0 and `deleted` = 0")
            ->groupBy('user_id', 'first_name_nom', 'last_name_nom')
            ->orderBy(['sum(char_all)', 'desc'])
            ->limit(1)
            ->query();
    }

    public static function FindLevelAll()
    {
        return App::getPBase()
            ->select('first_name_nom', 'last_name_nom', 'level', 'user_id')
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->where("`level` > 1 and `deleted` = 0")
            ->orderBy(['level', 'desc'])
            ->limit(1)
            ->query();
    }

    public static function FindLevel($peer_id)
    {
        return App::getPBase()
            ->select('first_name_nom', 'last_name_nom', 'level')
            ->from(static::$table)
            ->innerJoin(static::$table, 'user_id', 'users', 'id')
            ->where("`level` > 1 and `peer_id` = $peer_id and `deleted` = 0")
            ->orderBy(['level', 'desc'])
            ->limit(20)
            ->query();
    }

    public static function FindTopPeer()
    {
        return App::getPBase()->select(['peers.`id`'], 'title', ['sum(msg_all)', 'msg'], ['sum(char_all)', 'length'])
            ->from(static::$table)
            ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
            ->where(" 1 = 1")
            ->groupBy('peers.id', 'title')
            ->orderBy(['sum(char_all)', 'desc'])
            ->limit(1)
            ->query();
    }

    public static function FindTopPeerDay()
    {
        return App::getPBase()->select(['peers.`id`'], 'title', ['sum(msg_day)', 'msg'], ['sum(char_day)', 'length'])
            ->from(static::$table)
            ->innerJoin(static::$table, 'peer_id', 'peers', 'id')
            ->where(" 1 = 1")
            ->groupBy('peers.id', 'title')
            ->orderBy(['sum(char_day)', 'desc'])
            ->limit(1)
            ->query();
    }

    public function createCallback($action, $time, $params = [])
    {
        return App::getPBase()
            ->insert('callback')
            ->column(['action', 'params', 'tst', 'user_id', 'peer_id'])
            ->value([$action, json_encode($params), App::replaceSpecialChars($time), $this->user_id, $this->peer_id])
            ->run();
    }

    public function getCallback($action, $useUser = false, $usePeer = false)
    {
        $where[] = "action = '$action'";
        if($useUser)
            $where[] = "user_id = '$this->user_id'";
        if($usePeer)
            $where[] = "peer_id = '$this->peer_id'";
        return App::getPBase()
            ->select('action')
            ->from('callback')
            ->where(implode(' and ', $where))
            ->queryOne('action');
    }

    public function getRoleName()
    {
        return App::getPBase()
            ->select('title')
            ->from(Role::$table)
            ->where("`id` = '$this->role_id'")
            ->queryOne('title');
    }

    public function haveAccess($access_id): bool
    {
        if ($this->role_id == Role::MAIN_ADMIN)
        {
            return true;
        }
        $res = App::getPBase()
            ->select('role_id')
            ->from('roles_access')
            ->where("`access_id` = '$access_id' and `role_id` = '$this->role_id'")
            ->queryOne('role_id');
        return !is_null($res);
    }
}