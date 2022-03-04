<?php

namespace core;

use api\Vk;
use model\Group;
use model\User;
use model\Peer;
use comboModel\UserPeer;
use Exception;
use ReflectionMethod;

/**
 * Class Controller
 * @property Vk $vk
 * @property User $user
 * @property Peer $peer
 * @property UserPeer $userPeer
 */

class Controller
{
    public static bool $isGlobal = false;
    protected Vk $vk;
    protected User $user;
    protected Peer $peer;
    protected UserPeer $userPeer;
    protected static string $classUser = 'model\User';
    protected static string $classGroup = 'model\Group';

    public function __construct(Vk $vk, User $user, Peer $peer, UserPeer $userPeer)
    {
        $this->vk = $vk;
        $this->user = $user;
        $this->peer = $peer;
        $this->userPeer = $userPeer;
    }

    public function run($action_name, $args = [])
    {
        try {
            $method = new ReflectionMethod(static::class, $action_name);
            $sorted_args = [];
            foreach ($method->getParameters() as $parameter) {
                if (array_key_exists($parameter->getName(), $args)){
                    $sorted_args[]=$args[$parameter->getName()];
                }
                else {
                    if (!$parameter->isDefaultValueAvailable()) {
                        throw new Exception("Параметр " . $parameter->getName() . " не имеет знаение по умолчанию, поэтому надо его передавать");
                    }
                    else {
                        $sorted_args[] = $parameter->getDefaultValue();
                    }
                }
            }
            return call_user_func_array([$this, $action_name], $sorted_args);
        }
        catch (Exception $e)
        {
            echo $e;
            file_put_contents('main_log.log', $e->getMessage());
            return false;
        }
    }

    protected function render($filename, $variables = []){
        $file = "view/$filename.php";
        foreach($variables as $key=>$value)
            $$key = $value;
        if(file_exists($file)){
            ob_start();
            include $file;
            return ob_get_clean();
        }
        return '';
    }

    protected function getIdFromMessage($object)
    {
        $id = 0;
        if(isset($object['message']['reply_message']))
            $id = $object['message']['reply_message']['from_id'];
        if(isset($object['message']['fwd_messages'][0]))
            $id = $object['message']['fwd_messages'][0]['from_id'];
        return $id;
    }

//    protected function curlExec($url, $data = [], $headers = [])
//    {
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//        $server_output = curl_exec($ch);
//        curl_close ($ch);
//        return $server_output;
//    }

    protected function getObjFromMessage($user_text)
    {
        $regUser = "~\[id(?<id>[0-9]*)\|[^\[\]\|]*\]~";
        $regStarGroup = "~\[club(?<id>[0-9]*)\|[^\[\]\|]*\]~";
        $regUrl = "~https://vk.com/(?<domain>.*)~";
        $regId = "~https://vk.com/id(?<id>[0-9]{1,})$~";
        $regGroup = "~https://vk.com/(?:club|public)(?<id>[0-9]*)~";
        if (preg_match($regUser, $user_text, $matches)) {
            $user_id = intval($matches['id']);
            $user = User::findById($user_id);
        } elseif (preg_match($regStarGroup, $user_text, $matches)) {
            $user_id = intval(-1*$matches['id']);
            $group = Group::findById($user_id);
        } elseif (preg_match($regId, $user_text, $matches)) {
            $user = User::findById($matches['id']);
        } elseif (preg_match($regGroup, $user_text, $matches)) {
            $group = Group::findById(-1*$matches['id']);
        } elseif (preg_match($regUrl, $user_text, $matches)) {
            $user = User::findByDomain($matches['domain']);
            if($user === false)
                $group = Group::findByDomain($matches['domain']);
        } else {
            $user = User::findById(intval($user_text));
        }
        return $user??$group??false;
    }

    public function getUserFromMessage($user_text, $object)
    {
        $obj = $this->getObjFromMessage($user_text);
        if($obj === false || !$obj->isUser())
        {
            $id = $this->getIdFromMessage($object);
            if($id == 0)
            {
                return false;
            }
            $obj = User::findById($id);
        }
        return $obj;
    }

    public function getGroupFromMessage($user_text, $object)
    {
        $obj = $this->getObjFromMessage($user_text);
        if($obj === false || $obj->isUser())
        {
            $id = $this->getIdFromMessage($object);
            $obj = Group::findById($id);
        }
        return $obj;
    }
}