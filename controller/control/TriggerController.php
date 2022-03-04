<?php


namespace controller\control;

use core\Controller;
use model\Role;
use model\Trigger;
use core\Response;
use core\App;

class TriggerController extends Controller
{
    private int $globalId = 2000000001;
    public static bool $isGlobal = true;

    public function createAction($object, $user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if(isset($object['message']['reply_message']))
            $message = $object['message']['reply_message'];
        if(isset($object['message']['fwd_messages']) && count($object['message']['fwd_messages']) > 0)
            $message = $object['message']['fwd_messages'][0];
        if (isset($message) && (
                (isset($message['text']) && $message['text'] != '') ||
                (isset($message['attachments']) && $message['attachments'] != [] && isset($message['attachments'][0]))
            )) {
            if (isset($message['attachments']) && isset($message['attachments'][0]))
                $attachment = $message['attachments'][0];
            if (isset($message['text']))
                $message = $message['text'];
            $peer_id = $this->peer->id;
            $user_text = mb_strtolower($user_text);
            if($this->peer->id == $this->globalId)
            {
                $peer_id = 0;
                $trigger = Trigger::findByCommand($user_text, $peer_id);
            }
            else {
                $trigger = Trigger::findByCommandAndPeer($user_text, $peer_id);
                if($trigger === false)
                {
                    $trigger = Trigger::findByGlobal($user_text);
                }
            }
            if ($trigger === false) {
                $actions = json_decode(file_get_contents('config/action.json'), true);
                $commands = explode(' ', trim($user_text));
                if (!isset($actions[$commands[0]])) {
                    if ($this->userPeer->haveAccess(Role::TRIGGER_EDITOR_ACCESS) || $this->user->is_dev == 1) {
                        $trigger = new Trigger();
                        $trigger->peer_id = $peer_id;
                        $trigger->command = $user_text;
                        if (isset($message))
                            $trigger->text_trigger = $message;
                        if (isset($attachment))
                            $trigger->attach = $attachment['type'] . $attachment[$attachment['type']]['owner_id'] . '_' . $attachment[$attachment['type']]['id'];
                        $result = $trigger->save();
                        if ($result === false)
                            $response->message = 'Ошибка при создании';
                        elseif ($peer_id === 0)
                            $response->message = 'Глобальный триггер создан';
                        else
                            $response->message = 'Локальный триггер создан';
                    } else
                        $response->message = 'У вас нет доступа к созданию триггеров!';
                } else
                    $response->message = 'Нельзя использовать служебное слово в названии триггера';
            } else
                $response->message = "Триггер с такой командой уже создан глобально или в данной беседе.";
        }
        if ($response->message == '')
            $response->message = 'Не выбрано сообщение';
        return $response;
    }

    public function deleteAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $peer_id = $this->peer->id;
        if($this->peer->id == $this->globalId)
            $peer_id = 0;
        $trigger = Trigger::findByCommandAndPeer($user_text, $peer_id);
        if ($trigger !== false && ($this->userPeer->haveAccess(Role::TRIGGER_EDITOR_ACCESS) || $this->user->is_dev == 1)) {
            $result = $trigger->delete();
            if ($result !== false) {
                $response->message = 'Триггер удален';
            }
        } else
            $response->message = 'Триггер не найден';
        return $response;
    }

    public function getAction($object): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $name = App::replaceSpecialChars(($object['message']['text']));
        $trigger = Trigger::findByCommand($name, $this->peer->id);
        if ($trigger !== false)
        {
            $response->message = $trigger->text_trigger;
            $response->attachment = $trigger->attach;
        }
        return $response;
    }

    public function allTriggerAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '')
            if ($this->peer->id == $this->globalId) {
                if (!isset($triggers['command'])) {
                    $triggers = Trigger::findAllTriggers(0);
                    $message = $this->render('top/Triggers', [
                        'triggers' => $triggers,
                        'title' => 'Глобальные триггеры:'
                    ]);
                    $response->message = $message;
                } else
                    $response->message = "Триггеров в данной беседе нет.";
            } else {
                $triggers = Trigger::findAllTriggers($this->peer->id);
                if (!isset($triggers['command'])) {
                    $message = $this->render('top/Triggers', [
                        'triggers' => $triggers,
                        'title' => 'Триггеры в данной беседе:'
                    ]);
                    $response->message = $message;
                } else
                    $response->message = "Триггеров в данной беседе нет.";
            }
        return $response;
    }
}