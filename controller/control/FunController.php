<?php

namespace controller\control;

use core\Controller;
use core\Response;

class FunController extends Controller
{
    public function battleAtkAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if($this->user->is_dev)
        {
            $data = explode(' ', $user_text);
            $response->message = $this->calcBattleAtk($data[0], $data[1], $data[2], $data[3]??0);
        }
        return $response;
    }

    private function calcBattleAtk($atk, $goldEarn, $goldBattle, $goldPrev = 0): string
    {
        $res = ($atk / ($goldEarn + ($goldPrev * (1 / 3)))) * $goldBattle;
        return $res;
    }

    public function battleDefAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if($this->user->is_dev)
        {
            $data = explode(' ', $user_text);
            $response->message = $this->calcBattleDef($data[0], $data[1], $data[2], $data[3]??0);
        }
        return $response;
    }

    private function calcBattleDef($def, $goldEarn, $goldBattle, $goldPrev = 0): string
    {
        $res = ($def / ($goldEarn + ($goldPrev * (1 / 3)))) * $goldBattle;
        return $res;
    }
}