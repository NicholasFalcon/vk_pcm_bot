<?php


namespace controller\fun;

use Model\ClanMember;
use Controller;
use model\Clan;
use model\GlobalParameters;
use model\Hero;
use model\Trigger;
use model\User;
use Response;


class MmoRPGController extends Controller
{
    public function getMyHeroAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        $check = 0;
        if ($user_text == '' && $this->user->is_dev == 1) {
            if ($hero != false) {
//                if ((time()) - $hero->stamina_tst <= 0 && $hero->stamina != $hero->max_stamina) {
//                    while (time() - $hero->stamina_tst <= 0) {
//                        $hero->stamina_tst = $hero->stamina_tst + 3600;
//                        $hero->save();
//                        $check ++;
//                    }
//                    if ($check >= 1 && $check <= $hero->max_stamina) {
//                        $hero->stamina = $hero->stamina + $check;
//                        $hero->save();
//                        if ($hero->stamina >= $hero->max_stamina) {
//                            while ($hero->stamina == $hero->max_stamina) {
//                                $hero->stamina = $hero->stamina - 1;
//                                $hero->save();
//                            }
//                        }
//                    }
//                }
                $response->message = $this->render('user/hero', [
                    'hero' => $hero,
                    'user' => $this->user,
                    'userPeer' => $this->userPeer,
                ]);
                $response->setButtonRow(['Атака','1'],['Защита','2']);
                $response->setButtonRow(['Банда','3'],['Рейды','4']);
                $response->setButtonRow(['Николаич','5'],['Чёрный рынок','6']);
                $response->setButtonRow(['Верстак','7'],['Мега тайничок','8']);
                if ($hero->exp >= (15 * 2.3 * $hero->level * 4))
                    $response->setButtonRow(['Повышение', 'LevelUp']);
            } else
                $response->message = "У вас пока нет профиля, пропишите +рег";
        }
        return $response;
    }

    public function RaidsAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1) {
            $response->message = "Лес - 5-10 минут" . PHP_EOL
            ."В лесу можно найти не только ягоды..." . PHP_EOL
            .PHP_EOL
            ."Мегаполис - 30 минут" . PHP_EOL
            ."Как бы вы не пытались что-то найти, но здесь нет лёгко добычи... Лишь смелый полезет в Самолёт!"
            .PHP_EOL
            .PHP_EOL
            ."Засада - до часа" . PHP_EOL
            ."Устроить засаду. Может какая-то наивная пешка да попадёт в ваш капкан."
            .PHP_EOL
            .PHP_EOL
            ."Уборочка" . PHP_EOL
            ."Надоели что пауки спят с вами в одной кровати? Не беда! Сделай правильный выбор и вышверни надоедливых нахлебников!";
            $response->setButtonRow(['Лес', '1'], ['Мегаполис', '2']);
            $response->setButtonRow(['Засада', '3'], ['Уборочка', '4']);
            $response->setButtonRow(['Арена', '5'], ['Герой', '0']);
        }
        return $response;
    }

    public function TheGangAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "В разработке...";
        $response->message = $message;
        return $response;
    }

    public function AttacksAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        $message = "Выбери цель, смелый воин...";
        $x = 0;
        $text = array(
            "Живой Природы",
            "Кровавого Пера",
            "Ночных теней",
            "Цветущих роз",
        );
        $response->message = $message;
        $response->setButtonRow($text[0]);
        return $response;
    }

    public function DefenseAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        $hero->target = $hero->national;
        $hero->save();
        if ($hero->target = 1)
            $national = "Живой Природы";
        elseif ($hero->target == 2)
            $national = "Кровавого Пера";
        elseif ($hero->target == 3)
            $national = "Ночных Теней";
        elseif ($hero->target == 4)
            $national = "Цветущих Роз";
        $hero->status = "Защита {$national}.";
        $hero->save();
        $timeBattle = GlobalParameters::findById(4);
        $nextBattle = $timeBattle->param1 - time();
        if ($nextBattle >= 3600) {
            while ($nextBattle >= 3600) {
                $hours++;
                $nextBattle = $nextBattle - 3600;
            }
        }
        $minutes = round($nextBattle / 60);
        $message = "Вы встали на защиту своей Нации - {$national}. Следующая битва через {$hours} часа {$minutes} минут.";
        $response->message = $message;
        return $response;
    }

    public function BirshaAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function AuctionAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function CraftingAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function ShopsAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function StokeAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function LevelUpAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        $trigger = Trigger::findByCommand('Повышение');
        if ($hero != false) {
            if ($hero->exp >= (15 * 2.3 * $hero->level * 4)) {
                $hero->level = $hero->level + 1;
                $hero->save();
                $response->attachment = $trigger->attach;
            }
        }
        return $response;
    }

    public function setHeroAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        if ($hero === false) {
            $response->message = "Выберите сторону за которую будете воевать в этом мире, окутанным таинственным злом и загадками...";
            $response->setButtonRow(['Живой Природы','1'],['Кровавого Пера','2']);
            $response->setButtonRow(['Ночных Теней','3'],['Цветущих Роз','4']);
        }
        return $response;
    }


    public function WildLifeAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        if ($hero === false) {
            $response->message = "Отличный выбор, вы стали рейдером Живой природы! Удачи в покорении сиго чудного мира и не забудь надеть Подгузники:3 Салага.";
            $hero = new Hero();
            $hero->id = $this->user->id;
            $hero->class = "Новобранец";
            $hero->national = 1;
            $hero->save();
        }
        return $response;
    }

    public function BloodFeatherAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        if ($hero === false) {
            $response->message = "Отличный выбор, вы стали рейдером Кровавого Пера! Удачи в покорении сиго чудного мира и не забудь надеть Подгузники:3 Салага.";
            $hero = new Hero();
            $hero->id = $this->user->id;
            $hero->class = "Новобранец";
            $hero->national = 2;
            $hero->save();
        }
        return $response;
    }

    public function NightShadowsAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        if ($hero === false) {
            $response->message = "Отличный выбор, вы стали рейдером Ночных Теней! Удачи в покорении сиго чудного мира и не забудь надеть Подгузники:3 Салага.";
            $hero = new Hero();
            $hero->id = $this->user->id;
            $hero->class = "Новобранец";
            $hero->national = 3;
            $hero->save();
        }
        return $response;
    }

    public function BloomingRosesAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        if ($hero === false) {
            $response->message = "Отличный выбор, вы стали рейдером Цветущих роз! Удачи в покорении сиго чудного мира и не забудь надеть Подгузники:3 Салага.";
            $hero = new Hero();
            $hero->id = $this->user->id;
            $hero->class = "Новобранец";
            $hero->national = 4;
            $hero->save();
        }
        return $response;
    }

    public function ForestAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        $check = 0;
        if ($hero != false) {
            if ($hero->status == "Отдых" || $hero->status = "Защита {$hero->national}") {
                if ($hero->stamina >= 1) {
                    $this->vk->messagesSend($this->peer->id, "Вы ушли в рейд в Лес... Удачных поисков, Путник.");
                    $hero->status = "В поисках материалов.";
                    $hero->stamina = $hero->stamina - 1;
                    $hero->stamina_tst = time() + 3600;
                    $hero->save();
                    sleep(15);
                    $random_int = random_int(1, 4);
                    $exp = round($hero->level * 1.6, 0, PHP_ROUND_HALF_UP);
                    $hero = Hero::findById($this->user->id);
                    $hero->status = "Отдых";
                    $hero->exp = $hero->exp + $exp;
                    $hero->gold = $hero->gold + $random_int;
                    $hero->save();
                    $this->vk->messagesSend($this->peer->id, "Вы пришли из леса и получили {$exp} опыта, найдя {$random_int} золота.");
                } else
                    $this->vk->messagesSend($this->peer->id, "Не хватает сил.");
            } else
                $this->vk->messagesSend($this->peer->id, "Ты уже чем-то занят.");
        } else
            $this->vk->messagesSend($this->peer->id, "Вы ушли в рейд в лес...");
        return $response;
    }

    public function MegapolisAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $hero = Hero::findById($this->user->id);
        $check = 0;
        if ($hero != false) {
            if ($hero->status == "Отдых" || $hero->status = "Защита {$hero->national}") {
                if ($hero->stamina >= 1) {
                    $this->vk->messagesSend($this->peer->id, "Вы ушли в столицу, надеясь найти что-то новое...");
                    $hero->status = "Гуляет по столице {$hero->national}... ";
                    $hero->stamina = $hero->stamina - 1;
                    $hero->stamina_tst = time() + 3600;
                    $hero->save();
                    sleep(15);
                    $random_int = random_int(1, 4);
                    $exp = round($hero->level * 1.6, 0, PHP_ROUND_HALF_UP);
                    $hero = Hero::findById($this->user->id);
                    $hero->status = "Отдых";
                    $hero->exp = $hero->exp + $exp;
                    $hero->gold = $hero->gold + $random_int;
                    $hero->save();
                    $this->vk->messagesSend($this->peer->id, "Вы пришли из столицы и получили {$exp} опыта, найдя {$random_int} золота.");
                } else
                    $this->vk->messagesSend($this->peer->id, "Не хватает сил.");
            } else
                $this->vk->messagesSend($this->peer->id, "Ты уже чем-то занят.");
        } else
            $this->vk->messagesSend($this->peer->id, "Вы ушли в рейд в лес...");
    }

    public function AmbushAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function CleaningAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "test";
        $response->message = $message;
        return $response;
    }

    public function FightingAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = "Вы пришли на арену в поисках лёгких денег.";
        $response->message = $message;
        return $response;
    }
}