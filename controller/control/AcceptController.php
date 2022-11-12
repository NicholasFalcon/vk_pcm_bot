<?php


namespace controller\control;

use core\Controller;
use model\UserConfirmation;
use model\Web;
use core\Response;

class AcceptController extends Controller
{
    public function listAction($user_text = ''): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = '';
        $page = intval($user_text);
        $confirmations = UserConfirmation::findAllObjByUser($this->user->id, $page);
        foreach ($confirmations as $confirmation) {
            /**
             * @var $confirmation UserConfirmation
             */
            $message .= "$confirmation->id) {$confirmation->getTitle()}" . PHP_EOL;
            $response->setButtonRow(["Подтверждаю $confirmation->id", "accept_$confirmation->id"], ["Отказываюсь $confirmation->id", "decline_$confirmation->id"]);
        }
        $response->message = $message;
        $count = UserConfirmation::getCountPagesByUser($this->user->id);
        if ($page <= 0) {
            $response->setButtonRow(["Подтверждения " . ($page + 1), "acceptation_" . ($page + 1)]);
        } elseif ($page >= $count) {
            $response->setButtonRow(["Подтверждения " . ($page - 1), "acceptation_" . ($page - 1)]);
        } else {
            $response->setButtonRow(["Подтверждения " . ($page - 1), "acceptation_" . ($page - 1)], ["Подтверждения " . ($page + 1), "acceptation_" . ($page + 1)]);
        }
        return $response;
    }

    public function indexAction($id): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $confirmation = new UserConfirmation($id);
        if ($confirmation->isExists()) {
            if ($confirmation->user_id == $this->user->id) {
                $action = $confirmation->getAction();
                $result = call_user_func_array([$this, $action], ['confirmation' => $confirmation]);
                if ($result === true)
                    $response->message = 'Подтверждено!';
                else
                    $response->message = 'Была ошибка, обратитесь к разработчику!';
            }
        }
        return $response;
    }

    private function webConfirm(UserConfirmation $confirmation)
    {
        $data = $confirmation->getData();
        if (isset($data['web_id']) && $data['web_id'] > 0) {
            $web = new Web($data['web_id']);
            if ($web->isExists()) {
                $this->peer->web_id = $web->id;
                $this->peer->save();
                $confirmation->delete();
                return true;
            }
        }
        return false;
    }
}