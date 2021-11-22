<?php

namespace model;

use comboModel\UserPeer;
use core\App;
use core\Model;

/**
 * @property int $owner_id
 * @property string $title
 */
class Role extends Model
{
    static string $table = 'roles';

    public const USER = 1;
    public const MAIN_ADMIN = 2;

    public const TRIGGER_EDITOR_ACCESS = 1;
    public const KICK_ACCESS = 2;
    public const BAN_ACCESS = 3;
    public const PRED_ACCESS = 4;
    public const PEER_MESSAGE_ACCESS = 5;
    public const ROLE_EDITOR_ACCESS = 6;
    public const MUTE_ACCESS = 7;
    public const IMMUNE_ACCESS = 8;

    public const TRIGGER_EDITOR_ACCESS_TITLE = 'Управление триггерами';
    public const KICK_ACCESS_TITLE = 'Удаление пользователей';
    public const BAN_ACCESS_TITLE = 'Блокировка пользователей';
    public const PRED_ACCESS_TITLE = 'Выдача предупреждений пользователям';
    public const PEER_MESSAGE_ACCESS_TITLE = 'Общение между беседами';
    public const ROLE_EDITOR_ACCESS_TITLE = 'Изменение роли участников';
    public const MUTE_ACCESS_TITLE = 'Заглушение пользователей';
    public const IMMUNE_ACCESS_TITLE = 'Иммунитет к командам';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new Role($data);
        }
        return false;
    }

    public static function findAllByOwnerId($owner_id)
    {
        return App::getPBase()
            ->select('title', 'id')
            ->from(static::$table)
            ->where("`owner_id` = '$owner_id'")
            ->orderBy(['id', 'asc'])
            ->query();
    }

    public static function findAllToChange($owner_id)
    {
        return App::getPBase()
            ->select('title', 'id')
            ->from(static::$table)
            ->where("`owner_id` = '$owner_id' or `owner_id` = '0'")
            ->orderBy(['id', 'asc'])
            ->query();
    }

    public static function getCountByOwnerId($owner_id)
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`owner_id` = '$owner_id'")
            ->queryOne('count');
    }

    public function haveAccess($access_id): bool
    {
        $res = App::getPBase()
            ->select('role_id')
            ->from('roles_access')
            ->where("`access_id` = '$access_id' and `role_id` = '$this->id'")
            ->queryOne('role_id');
        return !is_null($res);
    }

    public function setAccess($access_id)
    {
        App::getPBase()
            ->insert('roles_access')
            ->column(['role_id', 'access_id'])
            ->value([$this->id, $access_id])
            ->run();
    }

    public function revokeAccess($access_id)
    {
        App::getPBase()
            ->delete('roles_access')
            ->where("`role_id` = '$this->id' and `access_id` = '$access_id'")
            ->run();
    }

    public function getAccesses()
    {
        return App::getPBase()
            ->select('access_id')
            ->from('roles_access')
            ->where("`role_id` = $this->id")
            ->query();
    }

    public function clearRole()
    {
        App::getPBase()
            ->update(UserPeer::$table)
            ->set('role_id', Role::USER)
            ->where("`role_id` = '$this->id'")
            ->run();
    }
}