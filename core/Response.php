<?php

namespace core;

use api\Vk;
use model\Peer;

class Response
{
    public string $message = '';
    public string $attachment = '';
    public bool $mute = false;
    public int $peer_id = 0;
    public array $buttons = [];
    public string $action = 'send_message';
    private bool $one_time = false;
    private bool $inline = true;

    public const PRIMARY = 'primary';
    public const SECONDARY = 'secondary';
    public const POSITIVE = 'positive';
    public const NEGATIVE = 'negative';

    public function __construct()
    {

    }

//    public function setList($list)
//    {
//        $this->buttons = [
//            [
//                "action" => [
//                    "text" => $list,
//                    "vkpay" => "vkpay",
//                    "open_app" => "open_app",
//                    "location" => "location",
//                    "open_link" => "open_link"
//                ]
//            ]
//        ];
//        $this->action = 'send_with_buttons';
//    }

    public function setButton($text, $value, $color = self::PRIMARY)
    {
        $peer_color = $this->getColor();
        if($peer_color != false)
        {
            $color = $peer_color;
        }
        $this->buttons = [
            [
                [
                    "action" => [
                        "type" => "text",
                        "payload" => json_encode(["pcmButtonAction" => $value]),
                        "label" => $text
                    ],
                    "color" => $color
                ]
            ]
        ];
        $this->action = 'send_with_buttons';
    }

    public function setButtonRow(...$buttons)
    {
        $resp = [];
        $color = self::PRIMARY;
        $peer_color = $this->getColor();
        if($peer_color != false)
        {
            $color = $peer_color;
        }
        foreach ($buttons as $button)
        {
            $resp[] = [
                "action" => [
                    "type" => "text",
                    "payload" => json_encode(["pcmButtonAction" => $button[1]]),
                    "label" => $button[0]
                ],
                "color" => $button[2]??$color
            ];
        }
        $this->buttons[] = $resp;
        $this->action = 'send_with_buttons';
    }

    public function setButtonOneTime()
    {
        $this->one_time = true;
    }

    public function setButtonFull()
    {
        $this->inline = false;
    }

    public function useAction(Vk $vk)
    {
        switch ($this->action) {
            case 'send_message':
                $this->sendMessage($vk);
                break;
            case 'send_with_buttons':
                $this->sendMessageButtons($vk);
                break;
        }
    }

    private function sendMessage(Vk $vk)
    {
        $vk->messagesSend($this->peer_id, $this->message, $this->attachment, '', false, $this->mute);
    }

    private function sendMessageButtons(Vk $vk)
    {
        $buttons = [
            "one_time" => $this->one_time,
            "buttons" => $this->buttons,
            "inline" => $this->inline
        ];
        $vk->messagesSend($this->peer_id, $this->message, $this->attachment, '', $buttons, $this->mute);
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        $peer = Peer::findById($this->peer_id);
        if($peer !== false)
        {
            $setting_value = $peer->getSetting(12);
            $color = self::POSITIVE;
            if ($setting_value == 1)
                $color = self::POSITIVE;
            elseif ($setting_value == 2)
                $color = self::NEGATIVE;
            elseif ($setting_value == 3)
                $color = self::SECONDARY;
            elseif ($setting_value == 4)
                $color = self::PRIMARY;
            return $color;
        }
        return false;
    }
}