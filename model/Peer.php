<?php


namespace model;

use comboModel\UserPeer;
use core\Model;
use core\App;

/**
 * Class Peer
 * @package model
 * @property string $title
 * @property int $owner_id
 * @property int $init
 * @property int $web_id
 * @property string $url
 * @property int autokick
 * @property int users_count
 * @property int users_count_old
 * @property int count_kick
 * @property int count_kick_old
 * @property string HelloMessage
 * @property string rules
 * @property int days
 * @property int MutePeer
 * @property string status5_name
 * @property string status4_name
 * @property string status3_name
 * @property string status2_name
 */
class Peer extends Model
{
    public static string $table = 'peers';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new Peer($data);
        }
        return false;
    }

    public function getSettings()
    {
        return App::getPBase()
            ->select('setting_id', 'value')
            ->from('peers_settings')
            ->where("peer_id = '$this->id'")
            ->orderBy(['setting_id', 'asc'])
            ->query();
    }

    public function getSetting($setting_id)
    {
        $result = App::getPBase()
            ->select()
            ->from('peers_settings')
            ->where("peer_id = '$this->id' and setting_id = '$setting_id'")
            ->queryOne('value');
        if ($result === false || $result === null) {
            $result = App::getPBase()
                ->select()
                ->from('webs_settings')
                ->where("web_id = '$this->web_id' and setting_id = '$setting_id'")
                ->queryOne('value');
            if ($result === false || $result === null) {
                $setting = new Setting($setting_id);
                if($setting->isExists())
                {
                    return $setting->default_value;
                }
            }
        }
        return $result;
    }

    public function initSettings()
    {
        $settings = json_decode(file_get_contents('config/settings.json'), true);
        foreach ($settings as $key => $setting)
        {
            App::getPBase()->insert('peers_settings')
                ->column(['peer_id', 'setting_id', 'value'])
                ->value([$this->id, $key, $setting['default']])
                ->run();
        }
    }

    public function setSetting($setting_id, $value)
    {
        $settings = json_decode(file_get_contents('config/settings.json'), true);
        if(isset($settings[$setting_id]))
        {
            if($settings[$setting_id]['type'] == 'boolean')
                if($value != '1' and $value != '0')
                    return 'error_type';
            return App::getPBase()->insert('peers_settings')
                ->column(['peer_id', 'setting_id', 'value'])
                ->value([$this->id, $setting_id, $value])
                ->onDuplicateKeyUpdate('value', $value)
                ->run();
        }
        else
            return 'error_not_found';
    }

    public static function findByWeb($web_id)
    {
        return App::getPBase()
            ->select('id', 'title', 'users_count', 'users_count_old', 'count_kick', 'count_kick_old')
            ->from(static::$table)
            ->where("`web_id` = '$web_id'")
            ->query();
    }

    public static function removeWeb($web_id)
    {
        return App::getPBase()
            ->update(static::$table)
            ->set('web_id', 0)
            ->where("`web_id` = '$web_id'")
            ->run();
    }

    public static function findAllAutokick()
    {
        return App::getPBase()
            ->select(['peer_id', 'id'])
            ->from('peers_settings')
            ->where("`setting_id`=".App::S_INACTIVE_KICK." and `value`=1")
            ->query();
    }

    public static function findCountInitPeer()
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`init` = 1")
            ->queryOne('count');
    }

    public static function findCountNotInitPeer()
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->where("`init` = 0")
            ->queryOne('count');
    }


    public static function PeerByUser($user_id)
    {
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->where ("`owner_id` = '%$user_id%'")
            ->query();
    }

    public static function RandPeer()
    {
       return App::getPBase()
            ->select()
            ->from(static::$table)
            ->query();
    }

    public static function findByName($name)
    {
        $name = App::replaceSpecialChars($name);
        return App::getPBase()
            ->select()
            ->from(static::$table)
            ->where("`title` like '%$name%'")
            ->limit(5)
            ->query();
    }

    public function getAdmins()
    {
        $roles = Role::findAllByOwnerId($this->owner_id);
        $roles = array_merge([['id' => Role::MAIN_ADMIN, 'title' => Role::MAIN_ADMIN_TITLE]], $roles);
        foreach ($roles as &$role)
        {
            $role['users'] = UserPeer::findAllByPeerAndRole($this->id, $role['id']);
        }
        return $roles;
    }

    public function getUsersByRole($role_id)
    {
        return App::getPBase()
            ->select('user_id')
            ->from(UserPeer::$table)
            ->where('role_id = '.intval($role_id))
            ->query();
    }
}