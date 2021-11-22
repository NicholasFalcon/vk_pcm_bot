<?php


namespace controller\control;

use core\Controller;
use model\UserConfirmation;
use core\Response;

class DeclineController extends Controller
{
    public function indexAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $confirmation = new UserConfirmation($user_text);
        if($confirmation->isExists())
        {
            if($confirmation->user_id == $this->user->id)
            {
                $action = $confirmation->getAction();
                $result = call_user_func_array([$this, $action], ['confirmation' => $confirmation]);
                if($result === true)
                    $response->message = 'Успешно отказано!';
                else
                    $response->message = 'Была ошибка, обратитесь к разработчику!';
            }
        }
        return $response;
    }

    private function webConfirm(UserConfirmation $confirmation)
    {
        $confirmation->delete();
        return true;
    }
}