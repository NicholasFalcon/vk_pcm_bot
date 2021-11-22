<?php


namespace model;

use core\Model;
use core\App;


/**
 * Class User
 * @package model
 * @property string $first_name_nom
 * @property string $last_name_nom
 * @property string $first_name_gen
 * @property string $last_name_gen
 * @property string $first_name_dat
 * @property string $last_name_dat
 * @property string $first_name_acc
 * @property string $last_name_acc
 * @property string $first_name_ins
 * @property string $last_name_ins
 * @property string $first_name_abl
 * @property string $last_name_abl
 * @property int $sex
 * @property string $domain
 * @property string $nick
 * @property string $pin
 * @property int $is_callable
 * @property int $is_dev
 * @property int $checker
 * @property string $first
 * @property string $second
 * @property string $country
 * @property int $stiker
 * @property int $black_list
 * @property string $user_action
 */
class User extends Model
{
    public static string $table = 'users';

    public static function findById($id)
    {
        $data = parent::findBy('id', $id);
        if(!is_null($data))
        {
            return new User($data);
        }
        return false;
    }

    public static function findByNick($nick)
    {
        $data = parent::findBy('nick', $nick);
        if(!is_null($data))
        {
            return new User($data);
        }
        return false;
    }

    public static function findByDomain($domain)
    {
        $data = parent::findBy('domain', $domain);
        if(!is_null($data))
            return new User($data);
        return false;
    }

    public function updateInfo(array $userInfo)
    {
        $this->first_name_nom = $userInfo['first_name_nom'];
        $this->last_name_nom = $userInfo['last_name_nom'];
        $this->sex = $userInfo['sex'];
        $this->domain = $userInfo['domain'];
        $this->first_name_gen = $userInfo['first_name_gen'];
        $this->last_name_gen = $userInfo['last_name_gen'];
        $this->first_name_dat = $userInfo['first_name_dat'];
        $this->last_name_dat = $userInfo['last_name_dat'];
        $this->first_name_acc = $userInfo['first_name_acc'];
        $this->last_name_acc = $userInfo['last_name_acc'];
        $this->first_name_ins = $userInfo['first_name_ins'];
        $this->last_name_ins = $userInfo['last_name_ins'];
        $this->first_name_abl = $userInfo['first_name_abl'];
        $this->last_name_abl = $userInfo['last_name_abl'];
        $this->save();
    }

    public function findName($id)
    {
        return App::getPBase()
            ->select('first_name_ins')
            ->from(static::$table)
            ->where("`id` = '$id'")
            ->queryOne('first_name_ins');
    }

    public function getName($pron = 'nom'): string
    {
        if($this->nick != '') {
            if($this->is_callable) {
                $res = "[id$this->id|$this->nick]";
            } else {
                $res = $this->nick;
            }
        }
        else
        {
            if($this->is_callable) {
                $name = $this->getNameText($pron);
                $res = "[id$this->id|$name]";
            } else {
                $res = $this->getNameText($pron);
            }
        }
        return $res;
    }

    public function getFullName($pron = 'nom', $notify = false): string
    {
        if($notify == false)
            $notify = $this->is_callable;
        if($notify)
        {
            $name = $this->getNameText($pron, true);
            $res = "[id$this->id|$name]";
        }
        else
        {
            $res = $this->getNameText($pron, true);
        }
        return $res;
    }

    public static function findNick()
    {
        return App::getPBase()
            ->select('first_name_nom', 'last_name_nom', 'nick')
            ->from(static::$table)
            ->where("`nick` is not null and `nick` != ''")
            ->limit(50)
            ->query();
    }

    public static function findCountUsers()
    {
        return App::getPBase()
            ->select(['count(*)', 'count'])
            ->from(static::$table)
            ->queryOne('count');
    }

    public function createNotification($message, $peer_id, $time)
    {
        return App::getPBase()
            ->insert('notification')
            ->column(['message', 'peer_id', 'tst'])
            ->value([$message, $peer_id, App::replaceSpecialChars($time)])
            ->run();
    }

    public function isUser(): bool
    {
        return true;
    }

    /**
     * @param $pron
     * @param bool $full
     * @return string
     */
    public function getNameText($pron, bool $full = false): string
    {
        if($full)
        {
            switch ($pron){
                case 'gen':
                    $name = $this->first_name_gen.' '.$this->last_name_gen;
                    break;
                case 'abl':
                    $name = $this->first_name_abl.' '.$this->last_name_abl;
                    break;
                case 'dat':
                    $name = $this->first_name_dat.' '.$this->last_name_dat;
                    break;
                case 'ins':
                    $name = $this->first_name_ins.' '.$this->last_name_ins;
                    break;
                case 'acc':
                    $name = $this->first_name_acc.' '.$this->last_name_acc;
                    break;
                default:
                    $name = $this->first_name_nom.' '.$this->last_name_nom;
                    break;
            }
        }
        else
        {
            switch ($pron) {
                case 'gen':
                    $name = $this->first_name_gen;
                    break;
                case 'abl':
                    $name = $this->first_name_abl;
                    break;
                case 'dat':
                    $name = $this->first_name_dat;
                    break;
                case 'ins':
                    $name = $this->first_name_ins;
                    break;
                case 'acc':
                    $name = $this->first_name_acc;
                    break;
                default:
                    $name = $this->first_name_nom;
                    break;
            }
        }
        return trim($name)==$name?$name:$this->id;
    }
}