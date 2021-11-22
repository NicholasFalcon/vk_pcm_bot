<?php


namespace controller\control;

use core\Controller;
use model\Bug;
use core\Response;

class DevController extends Controller
{
    public function devsAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = 'Вас приветствует команда разработки бота "Карманный чат менеджер":
[hironori|Николай Соколов]

По всем вопросам обращаться к нему, но не наглеть<3';
        return $response;
    }

    public function addBugAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev) {
            $bug = new Bug();
            $bug->info = $user_text;
            $bug->save();
            $response->message = 'Баг записан';
        }
        return $response;
    }

    public function bugListAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev) {
            $bugs = Bug::findAll();
            $response->message = '';
            foreach ($bugs as $bug) {
                $response->message .= "{$bug['id']}) {$bug['info']}" . PHP_EOL;
            }
        }
        return $response;
    }
}