<?php


namespace controller\fun;

use core\Controller;
use model\Clan;
use model\User;
use core\Response;


class ClanAndHeroController extends Controller
{
    public function createClanAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $new_clan = Clan::findClan($user_text);
        $clan = Clan::findClanByMember($this->user->id);
        if ($clan === false) {
            if ($new_clan === false) {
                if (mb_strlen($user_text) <= 25) {
                    $new_clan = new Clan();
                    $new_clan->title = $user_text;
                    $new_clan->owner_id = $this->user->id;
                    $new_clan->save();
                    $new_clan = Clan::findClan($user_text);
                    $new_clan->addNewMember($this->user->id);
                    $number = $new_clan->findCountMember();
                    $response->message = "Клан Создан: {$user_text}"
                        . PHP_EOL . "Чтоб пригласить человека в клан нужно написать: 'Пригласить в клан и упоминуть человека или переслать любое его сообщение'."
                        . PHP_EOL . "На данный момент там {$number} участник и это вы:3." . print_r($clan,1);
                } else
                    $response->message = "Слишком длинное название.";
            } else
                $response->message = "Это название уже занято.";
        } else
            $response->message = "Вы уже находитесь в клане.";
        return $response;
    }

    public function AllClanAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clans = Clan::findAllClan();
        $message = $this->render('top/clans', [
            'clans' => $clans,
            'title' => 'Топ 5 кланов по Славе:'
        ]);
        $response->message = $message;
        return $response;
    }

    /**
     * @param $object
     * @param $user_text
     * @return Response
     * user_text является ссылкой на пользователя, пускай ему в лс отправляется сообщение с приглашением, сам войти не
     * сможет
     * P.S сделай чтобы можно было писать в лс бота мол согласен в клан или нет!!!!
     */

    public function joinClanAction($object, $user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $regUser = "~\[id(?<id>[0-9]*)\|[^\[\]\|]*\]~";
        $regUrl = "~https://vk.com/(?<domain>.*)~";
        $regId = "~https://vk.com/id(?<id>[0-9]*)~";
        if (preg_match($regUser, $user_text, $matches)) {
            $user_id = intval($matches['id']);
            $user = new User($user_id);
        } elseif (preg_match($regId, $user_text, $matches)) {
            $user = User::findById($matches['id']);
        } elseif (preg_match($regUrl, $user_text, $matches)) {
            $user = User::findByDomain($matches['domain']);
        } else {
            $user = new User(intval($user_text));
        }
        $clan = Clan::findClanByMember($this->user->id);
        $id = $this->getIdFromMessage($object);
        if ($user_text == '' && $id >= 0) {
            if ($clan !== false) {
                $number = $clan->findCountMember();
                $max_member = $clan->findMaxMember($clan->title);
                if ($number < $max_member) {
                    if ($this->user->id == $clan->owner_id) {
                        $users = Clan::findClanByMember($id);
                        if ($users === false) {
                            $member = $clan->addNewMember($id);
                            if ($member)
                                $this->vk->messagesSend($id, "Вас пригласили в клан: {$clan->title}");
                            else
                                $response->message = "Ошибка!";
                        } else
                            $response->message = "Человек уже в клане.";
                    } else
                        $response->message = "Вы не создатель данного клана";
                } else
                    $response->message = "В клане больше нет мест.";
            } else
                $response->message = "Клан не найден";
        } elseif ($user->isExists() && $user->id >= 0) {
            if ($clan !== false) {
                $number = $clan->findCountMember();
                $max_member = $clan->findMaxMember($clan->title);
                if ($number < $max_member) {
                    if ($this->user->id == $clan->owner_id) {
                        $users = Clan::findClanByMember($user->id);
                        if ($users === false) {
                            $member = $clan->addNewMember($user->id);
                            if ($member) {
                                $this->vk->messagesSend($user->id,"Вас пригласили в клан: {$clan->title}");
                            } else
                                $response->message = "Ошибка!";
                        } else
                            $response->message = "Человек уже в клане.";
                    } else
                        $response->message = "Вы не создатель данного клана";
                } else
                    $response->message = "В клане больше нет мест.";
            } else
                $response->message = "Клан не найден";
        }
        return $response;
    }

    public function kickUserAction($object, $user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $regUser = "~\[id(?<id>[0-9]*)\|[^\[\]\|]*\]~";
        $regUrl = "~https://vk.com/(?<domain>.*)~";
        $regId = "~https://vk.com/id(?<id>[0-9]*)~";
        if (preg_match($regUser, $user_text, $matches)) {
            $user_id = intval($matches['id']);
            $user = new User($user_id);
        } elseif (preg_match($regId, $user_text, $matches)) {
            $user = User::findById($matches['id']);
        } elseif (preg_match($regUrl, $user_text, $matches)) {
            $user = User::findByDomain($matches['domain']);
        } else {
            $user = new User(intval($user_text));
        }
        $id = $this->getIdFromMessage($object);
        $clan = Clan::findClanByMember($this->user->id);
        if ($user_text == '' && $id >= 0) {
            if ($clan->isExists() && $this->user->id == $clan->owner_id) {
                $clan->kickUser($id);
                $response->message = "[id{$id}|Пользователь] был исключён из клана {$clan->title}.";
            }
        } elseif ($user->isExists() && $user->id >= 0) {
            if ($clan->isExists() && $this->user->id == $clan->owner_id) {
                $clan->kickUser($user->id);
                $response->message = "[id{$user->id}|Пользователь] был исключён из клана {$clan->title}.";
            }
        }
        return $response;
    }

    public function setTitleAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $new_clan = Clan::findClan($user_text);
        $clan = Clan::findClanByMember($this->user->id);
        if ($new_clan === false) {
            if ($clan->isExists()) {
                if (mb_strlen($user_text) <= 15) {
                    Clan::UpdateTitle($user_text, $this->user->id);
                    $response->message = "Название в клане поменялось на: {$user_text}";
                } else
                    $response->message = "Слишком длинное название клана.";
            }
        } else
            $response->message = "Это название уже занято.";
        return $response;
    }

    public function myClanAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clanId = Clan::findClanByMember($this->user->id);
        if (!$clanId === false) {
            $message = $this->render('top/myClan', [
                'clan' => $clanId
            ]);
            if ($clanId->id[0]['glory'] >= $clanId->id[0]['need_glory']
                && $this->user->id == $clanId->id[0]['owner_id']
                && $clanId->id[0]['level'] != $clanId->id[0]['max_level']) {
                $response->message = $message;
                $response->setButton('Поднять уровень клана', "LevelUp");
            } else {
                $response->message = $message;
            }
        } else
            $response->message = 'Вы не состоите в клане.';
        return $response;
    }

    public function levelUpClanAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clan = Clan::findClanByMember($this->user->id);
        if ($clan->isExists()) {
            if ($clan->glory >= $clan->need_glory && $this->user->id == $clan->owner_id) {
                if ($clan->level != $clan->max_level) {
                    $max_member = $clan->max_member;
                    $clan->level = $clan->level + 1;
                    $clan->glory = $clan->glory - $clan->need_glory;
                    $clan->max_member = $max_member + 2;
                    $clan->need_glory = 150 * 2.4 * ($clan->level * 3);
                    $clan->save();
                    $response->message = "Уровень клана {$clan->title} повышен.";
                } else
                    $response->message = "Клан имеет максильманый уровень";
            }
        }
        return $response;
    }

    public function leaveClanAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clan = Clan::findClanByMember($this->user->id);
        if($clan->isExists()) {
            if ($this->user->id != $clan->owner_id){
                $clan->kickUser($this->user->id);
                $response->message = "Вы вышли из клана: {$clan->title}.";
            } else
                $response->message = "Вы создатель клана {$clan->title}.";
        } else
            $response->message = "Вы не находитесь в клане";
        return $response;
    }

    public function removeClanAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clan = Clan::findClanByMember($this->user->id);
        if ($this->user->id == $clan->owner_id) {
            if ($clan->isExists()) {
                if ($clan->findCountMember() == 1) {
                    $clan->kickUser($this->user->id);
                    $clan->delete();
                    $response->message = "Клан {$clan->title} успешно удалён.";
                } else
                    $response->message = "В клане есть участники.".PHP_EOL."Удаление невозможно.";
            }
        } else
            $response->message = "Вы не создатель клана.";
        return $response;
    }

    public function allMemberByClanAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clan = Clan::findClanByMember($this->user->id);
        if($clan->isExists()) {
            $members = $clan->findMembers();
            $message = $this->render('top/member_clan', [
                'members' => $members,
                'title' => "Участники клана {$clan->title}:"
            ]);
            $response->message = $message;
        } else
            $response->message = 'Клан не найден!';
        return $response;
    }

    public function clanPinAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $clan = Clan::findClanByMember($this->user->id);
        if ($clan->isExists()) {
            if (strlen($user_text) <= 4 ){
                if ($this->user->id == $clan->owner_id ) {
                    $clan->clan_pin = $user_text;
                    $clan->save();
                    $response->message = "Значок {$user_text} установлен в клан {$clan->title}";
                } else
                    $response->message = "Вы не создатель клана";
            } else
                $response->message = "Где значок?";
        }
        return $response;
    }
}