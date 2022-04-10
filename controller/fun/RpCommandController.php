<?php

namespace controller\fun;

use core\Controller;
use model\Hero;
use model\User;
use core\Response;

class RpCommandController extends Controller
{
    public static bool $isGlobal = true;

    private function rpOnBody($id, $text)
    {
        $user = new User($id);
        $response = new Response();
        $response->message = "{$this->user->getName()} $text {$user->getName('acc')}";
//            $response->message = "Ты на кого быкуешь, Пёс, бан захотел?";
        $response->peer_id = $this->peer->id;
        return $response;
    }

    private function checkSex($boy, $girl, $id = false)
    {
        if ($id !== false) {
            if ($id > 0)
            {
                if ($this->user->sex == 1)
                    return $this->rpOnBody($id, $girl);
                else
                    return $this->rpOnBody($id, $boy);
            }
        } else
        {
            if ($this->user->sex == 1)
                return $this->rpOnSelf($girl);
            else
                return $this->rpOnSelf($boy);
        }
        return new Response();
    }

    private function rpOnSelf($text)
    {
        $response = new Response();
        $response->message = "{$this->user->getName()} $text";
        $response->peer_id = $this->peer->id;
        return $response;
    }

    public function suicideAction(): Response
    {

        return $this->checkSex("покончил с собой", "покончила с собой");
    }

    public function inviteAction(): Response
    {
        return $this->checkSex("Позвала на помощь", "позвал на помощь");
    }

    public function dressedAction(): Response
    {
        return $this->checkSex("оделся", "оделась");
    }

    public function gameAction($user_text): Response
    {
        return $this->checkSex("поиграл $user_text", "поиграла $user_text");
    }

    public function sleepAction(): Response
    {
        return $this->checkSex("лёг спать", "легла спать");
    }

    public function runAction($user_text): Response
    {
        return $this->checkSex("убежал в $user_text", "убежала в $user_text");
    }

    public function stalkAction(): Response
    {
        $hero = Hero::findById($this->user->id);
        $check = 0;
        if ($hero != false) {
            if ($hero->status == "Отдых") {
                if (($hero->stamina_tst - time()) <= 0 && $hero->stamina != $hero->max_stamina) {
                    while ($hero->stamina_tst - time() <= 0) {
                        $hero->stamina_tst = $hero->stamina_tst + 3600;
                        $hero->save();
                        $check++;
                    }
                    if ($check >= 1 && $check <= 5) {
                        $hero->stamina = $hero->stamina + $check;
                        $hero->save();
                        if ($hero->stamina > $hero->max_stamina) {
                            while ($hero->stamina == $hero->max_stamina) {
                                $hero->stamina = $hero->stamina - 1;
                            }
                        }
                    } else {
                        $hero->stamina = $hero->max_stamina;
                        $hero->save();
                    }
                }
                if ($hero->stamina != 0) {
                    $this->vk->messagesSend($this->peer->id, "Вы ушли в рейд.");
                    $hero->status = "В рейде.";
                    $hero->stamina = $hero->stamina - 1;
                    $hero->last_stalk_tst = time();
                    $hero->save();
                    sleep(5);
                    if (round(($hero->stamina_tst + 3600 - time()) / 60, 0, PHP_ROUND_HALF_UP) != 60) {
                        $hero->stamina_tst = time();
                        $hero->save();
                    }
                    $rand = rand(1, 4);
                    $exp = round($hero->level * 1.6, 0, PHP_ROUND_HALF_UP);
                    $hero = Hero::findById($this->user->id);
                    $hero->status = "Отдых";
                    $hero->exp = $hero->exp + $exp;
                    $hero->gold = $hero->gold + $rand;
                    $hero->save();
                    return $this->checkSex("пришел из рейда и получил {$exp} опыта, найдя {$rand} золота", "пришла из рейда и получила {$exp} опыта, найдя {$rand} золота");
                } else
                    return $this->checkSex("Не хватает сил.", "Не хватает сил.");
            } else
                return $this->checkSex("Ты уже в рейде.", "Ты уже в рейде.");
        } else
            return $this->checkSex("Ушел в рейд", "Ушла в рейд");
    }

    public function eatAction($user_text): Response
    {
        return $this->checkSex("съел $user_text", "съела $user_text и стала жирной:3");
    }

    public function sadAction(): Response
    {
        return $this->checkSex("грустит как маленькая девочка", "грустит");
    }

    public function hurtAction(): Response
    {
        return $this->checkSex("обиделся на всех и его надо бы кикнуть за это", "обиделась на всех:( успокойте её");
    }

    public function riseAction(): Response
    {
        return $this->checkSex("Воскрес", "Востала из мёртвых:3");
    }

    public function getAction($user_text): Response
    {
        return $this->checkSex("завёл $user_text", "завела $user_text");
    }

    public function growAction($user_text): Response
    {
        return $this->checkSex("отрастил $user_text", "отрастила $user_text");
    }

    public function cockAction($user_text): Response
    {
        return $this->checkSex("приготовил $user_text", "приготовила $user_text, и получиюла люлей от мужа...");
    }

    public function smashAction($user_text): Response
    {
        return $this->checkSex("разбил $user_text", "разбила $user_text, и получиюла люлей от мужа...");
    }

    public function smokeAction($user_text): Response
    {
        return $this->checkSex("покурил $user_text", "покурила и чуть не сдохла");
    }

    public function danceAction($user_text): Response
    {
        return $this->checkSex("станцевал $user_text", "Станцевала $user_text");
    }

    public function lookAction(): Response
    {
        return $this->checkSex("сел наблюдать, принесите кто-нибудь попкорн", "села наблюдать и жрёт попкорн в одну харю");
    }

    public function kaifAction(): Response
    {
        return $this->checkSex("подрочил и чуть не кончил", "подрочила челу напротив😱");
    }

    public function kaifReadyAction()
    {
        if ($this->user->id == '91737880' || $this->user->id == '338896450')
            return $this->checkSex("кончил увидев ножки Коленьки:P  ", "а Я не теку, я дрочу)");
        else
            return $this->checkSex("кончил что аж достал до потолка", "кончила вместе с тем кому дрочила :P ");
    }

    public function shelterAction(): Response
    {
        return $this->checkSex("спрятался ото всех", "спряталась, но бот знает где она ^_^");
    }

    public function drownsAction(): Response
    {
        return $this->checkSex("утонул в канаве.", "Прыгнула в озеро и не смогла утонуть, ибо оно вышло из берегов^_^");
    }

    public function foldAction(): Response
    {
        return $this->checkSex("сбросился с крыши дома", "сбросилась с крыши дома");
    }

    public function LowKissAction($object): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("чмокнул в щечку ", "нежно чмокнула ", $id);
    }

    public function TrahAction($object): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("чпокнул киску по имени ", "чпокнула ", $id);
    }

    public function dieAction(): Response
    {
        return $this->checkSex("умер в ужасных муках", "умерла и о ней  все забыли:(");
    }

    public function DressAction($user_text, $object): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("одел $user_text на", "одела $user_text на", $id);
    }

    public function DonAction($user_text): Response
    {
        return $this->checkSex("надел $user_text", "надела $user_text и отдалась случайному прохожему:((99(((");
    }

    public function DrinkAction($user_text): Response
    {
        if ($user_text != "водку")
            return $this->checkSex("выпил $user_text и вроде бы жив", "выпила $user_text и обсолютно ничего не произошло:))");
        else
            return $this->checkSex("выпил $user_text и набухался до отключки. Когда же его откачают?", "выпила $user_text и отдалась случайному прохожему:((99(((");
    }

    public function slapAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("шлепнул $user_text", "отшлепала $user_text", $id);
    }

    public function beatAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("ударил $user_text", "ударила $user_text", $id);
    }

    public function TakeAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("забрал $user_text у", "забрала $user_text у", $id);
    }

    public function KillAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("убил $user_text", "убила $user_text", $id);
    }

    public function superKissAction($object): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("засосал не получив по лицу от", "хотела б пососаться с", $id);
    }

    public function sendAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("послал $user_text", "послала $user_text", $id);
    }

    public function TouchAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("полапал $user_text не получив по лицу от", "полапала $user_text, а после пососалась", $id);
    }

    public function UndressAction($object): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("раздел, OH MY GOD", "раздела, но увидела лишь жирненький животик:(", $id);
    }

    public function superBeatAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("уебал $user_text", "уебала $user_text", $id);
    }

    public function superSexiAction($object): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("жестка трахнул", "трахнула в очко", $id);
    }

    public function eatingAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("покормил $user_text", "покормила $user_text", $id);
    }

    public function buryAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("закопал", "закопала", $id);
    }

    public function strokeAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("погладил $user_text", "погладила $user_text", $id);
    }

    public function shootAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("застрелил $user_text", "не получилось застрелить $user_text, оппонент выхватил оружие", $id);
    }

    public function KusAction($object)
    {
        $id = $this->getIdFromMessage($object);
        $x = rand(0, 1);
        if ($x == 1)
            return $this->checkSex("сделал кусь", "сделал нежный кусь", $id);
        else
            return $this->checkSex("сделал сильный кусь", "сделал сильный кусь", $id);
    }

    public function superKusAction($object, $user_text): Response
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("сильно укусил $user_text", "сильно укусила $user_text", $id);
    }

    public function ShootsAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("расстрелял ", "прасстреляла", $id);
    }

    public function SexAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("нагло трахнул", "попрыгала на ", $id);
    }

    public function superSexAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("изнасиловал человека и сел за решётку:( Помянем. Подбодрите", "изнасиловала, но увы 21 век....", $id);
    }

    public function toxicAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("не обронив ни слезинки отравил", "отравила, сев на лицо", $id);
    }

    public function kissAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("поцеловал", "засосала", $id);
    }

    public function SellAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("продал", "продала", $id);
    }

    public function shoveAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("пихнул член в", "запихнула", $id);
    }

    public function GiveAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("подарил $user_text ", "подарила $user_text", $id);
    }

    public function ListenAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("услышал как стонет ", "услышала стоны", $id);
    }

    public function SuperKickAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("сильно пизданул", "сильно пизданула", $id);
    }

    public function tickleAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("защекотал досметри", "защекотала досметри", $id);
    }

    public function BurnAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("сжёг заживо", "мстя за 15-17 века, сожгла на костре", $id);
    }

    public function PayAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("отдал $user_text ", "отдала $user_text у ", $id);
    }

    public function StealAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("украл $user_text у", "украла $user_text у ", $id);
    }

    public function GagAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("Заткнул ", "Заткнула ", $id);
    }

    public function LickAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("отлизал, но в ответ не получил ничего от ", "отлизала у ", $id);
    }

    public function SuckAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("отсосал у ", "отсосала за админку у ", $id);
    }

    public function ResurrectAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("воскресил", "воскресила ", $id);
    }

    public function BindAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("связал и понёс на кроватку ", "связала  ", $id);
    }

    public function hugAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("крепка обнял", "нежно обняла ", $id);
    }

    public function bustAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("арестовал за сексизм", "пыталась арестовать, но сама попала за решётку к ", $id);
    }

    public function plantAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("посадил $user_text", "посадила $user_text", $id);
    }

    public function hidetAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("спрятал $user_text", "спрятала $user_text", $id);
    }

    public function carryAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("унёс на ручках ", "унесла, думая что он вкусный и не станет жирной ", $id);
    }

    public function pokeAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("тыкнул", "тыкнула", $id);
    }

    public function pinchAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("ущипнул", "ущипнула", $id);
    }

    public function fastenAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("пристегнул $user_text", "пристегнула $user_text", $id);
    }

    public function strangleAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("задушил $user_text", "задушила $user_text", $id);
    }

    public function drownAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("пытался утопить, но понял что уже негде эту ", "утопила", $id);
    }

    public function throwAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("кинул $user_text в", "кинула $user_text в", $id);
    }

    public function punchAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("дал поджопник ", "сломав ногу пнула", $id);
    }

    public function cuddleAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("потискал $user_text", "потискала $user_text", $id);
    }

    public function haveAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("взял $user_text", "взяла $user_text", $id);
    }

    public function spitAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("плюнул в", "плюнула на", $id);
    }

    public function castrateAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("попытался кастрировать", "не обронив ни слизенки кастрировала", $id);
    }

    public function callAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("позвонил $user_text", "позвонила $user_text", $id);
    }

    public function warmAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("согрел $user_text", "согрела $user_text", $id);
    }

    public function superLickAction($object)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("облизал языком всё тело ", "облизала пресс", $id);
    }

    public function helpAction($object, $user_text)
    {
        $id = $this->getIdFromMessage($object);
        return $this->checkSex("вылечил $user_text", "вылечила $user_text", $id);
    }

    public function ShootsAllAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = 'Вас расстреляли! @all';
        return $response;
    }

    public function sexAllAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = 'Вас трахнули! @all';
        return $response;
    }

    public function killAllAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = 'Вас убили! @all';
        return $response;
    }

    public function burnAllAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = 'Вас сожгли! @all';
        return $response;
    }

    public function bustAllAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = 'Вас арестовали! @all';
        return $response;
    }
}