<?php


namespace model;

use core\Model;
use core\App;

/**
 * Class Group
 * @package model
 * @property int $have_ban
 * @property string $message
 * @property string $name
 * @property string $domain
 */
class Group extends Model
{
    public static string $table = 'groups';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new Group($data);
        }
        return false;
    }

    public static function findByDomain($domain)
    {
        $data = parent::findBy('domain', $domain);
        if(!is_null($data))
            return new Group($data);
        return false;
    }

    public function createPeer($peer_id)
    {
        App::getPBase()
            ->insert('groups_peer_info')
            ->column(['group_id', 'peer_id'])
            ->value([$this->id, $peer_id])
            ->run();
    }

    public function haveBan($peer_id): bool
    {
        $res = App::getPBase()
            ->select('have_ban')
            ->from('groups_peer_info')
            ->where("`group_id` = $this->id and `peer_id` = $peer_id")
            ->queryOne('have_ban');
        if($res == 1)
            return true;
        else
            return false;
    }

    public static function getGroupsActive($peer_id)
    {
        return App::getPBase()
            ->select('group_id')
            ->from("groups_peer_info")
            ->where("`peer_id` = $peer_id")
            ->query();
    }

    public function findByPeer($peer_id)
    {
        return App::getPBase()
            ->select()
            ->from('groups_peer_info')
            ->where("`group_id` = '$this->id' and `peer_id` = '$peer_id'")
            ->queryOne();
    }

    public function setBan($peer_id)
    {
        App::getPBase()
            ->update('groups_peer_info')
            ->set('have_ban', 1)
            ->where("`group_id` = '$this->id' and `peer_id` = '$peer_id'")
            ->run();
    }

    public function unsetBan($peer_id)
    {
        App::getPBase()
            ->update('groups_peer_info')
            ->set('have_ban', 0)
            ->where("`group_id` = '$this->id' and `peer_id` = '$peer_id'")
            ->run();
    }

    public function setDeleted($peer_id)
    {
        App::getPBase()
            ->update('groups_peer_info')
            ->set('deleted', 1)
            ->where("`group_id` = '$this->id' and `peer_id` = '$peer_id'")
            ->run();
    }

    public function unsetDeleted($peer_id)
    {
        App::getPBase()
            ->update('groups_peer_info')
            ->set('deleted', 0)
            ->where("`group_id` = '$this->id' and `peer_id` = '$peer_id'")
            ->run();
    }

    public function setAdmin($peer_id)
    {
        App::getPBase()
            ->update('groups_peer_info')
            ->set('is_admin', 1)
            ->where("`group_id` = '$this->id' and `peer_id` = '$peer_id'")
            ->run();
    }

    public function isAdmin($peer_id): bool
    {
        $res = App::getPBase()
            ->select('is_admin')
            ->from('groups_peer_info')
            ->where("`group_id` = $this->id and `peer_id` = $peer_id")
            ->queryOne('is_admin');
        if($res == 1)
            return true;
        else
            return false;
    }

    public function isUser(): bool
    {
        return false;
    }
}