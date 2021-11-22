<?php

namespace controller\fun;

use Response;
use Controller;
use model\Clan;
use model\Game;
use model\User;
use model\Words;

class GamesController extends Controller
{
    public function startGameAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame($user_text, $this->peer->id);
        if ($user_text != '') {
            if ($game != false) {
                if ($game->checker == 0) {
                    if ($user_text == 'виселица') {
                        $word = new Words();
                        $needword = strval($word->FindRandWord());
                        $game->checker = 1;
                        $game->wrong = null;
                        $game->right = null;
                        $game->need_word = $needword;
                        $game->save();
                        $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                        $topic = Words::findTopic($game->need_word);
                        $response->message = "Слово загадано." . PHP_EOL . "Тема: {$topic}" . PHP_EOL . $result;
                    } elseif ($user_text == 'загадки') {
                        $word = new Words();
                        $needword = strval($word->FindRandWord());
                        $game->checker = 2;
                        $game->wrong = null;
                        $game->right = null;
                        $game->need_word = $needword;
                        $game->save();
                        $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                        $topic = Words::findTopic($game->need_word);
                        $response->message = $topic . PHP_EOL . $result;
                    }
                } else {
                    $response->message = "Игра уже идёт";
                }
            } else {
                if ($user_text != '' && ($user_text == 'дуэль' || $user_text == 'виселица')) {
                    if ($user_text != 'дуэль') {
                        $game = new Game();
                        $game->peer_id = $this->peer->id;
                        $game->title = $user_text;
                        $game->checker = 0;
                        $game->save();
                        $response->message = "Игра {$game->title} была успешно подключена.\n Попробуйте снова запустить игру.";
                    } else {
                        $game = new Game();
                        $game->peer_id = $this->peer->id;
                        $game->title = $user_text;
                        $game->checker = 20;
                        $game->save();
                        $response->message = "Игра {$game->title} была успешно подключена.\n Попробуйте снова запустить игру.";
                    }
                }
            }
        }
        return $response;
    }

    public function stopGameAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame($user_text, $this->peer->id);
        if ($user_text != '') {
            if ($game != false) {
                if ($game->checker == 1) {
                    $game->checker = 0;
                    $game->wrong = null;
                    $game->right = null;
                    $game->save();
                    $member = Clan::findClanByMember($this->user->id);
                    if ($member->isExists()) {
                        $member->glory = $member->glory - 5;
                        $member->save();
                        $response->message = "Вы досрочно остановили игру :(" . PHP_EOL . "Слово: {$game->need_word}" . PHP_EOL . "Клан {$member->title} потерял 5 славы.";
                    } else {
                        $response->message = "Вы досрочно остановили игру :(" . PHP_EOL . "Слово: {$game->need_word}";
                    }
                } else {
                    $response->message = "Игра ещё не началась. Напишите игра старт 'название игры'.";
                }
            } else
                $response->message = "Игра не подключена.";
        }
        return $response;
    }

    public function GallowsAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame('виселица', $this->peer->id);
        if ((mb_strlen($user_text) == 1) && $game != false && $game->checker == 1) {
            if (stristr($game->need_word, $user_text)) {
                if (stristr($game->right, $user_text) || stristr($game->wrong, $user_text)) {
                    $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                    $response->message = "Это буква уже вводилась: {$user_text}" . PHP_EOL . "Слово: {$result}" . PHP_EOL . "Угадали: {$game->right}" . PHP_EOL . "Не угадали: {$game->wrong}" . PHP_EOL . "Жизни: " . (strlen($game->wrong)/2) . "/5";
                } else {
                    $game->right .= $user_text;
                    $game->save();
                    $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                    if ($result == $game->need_word) {
                        $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                        Game::updateGameStatus('виселица', $this->peer->id, 0);
                        $member = Clan::findClanByMember($this->user->id);
                        if ($member != false) {
                            $x = (mb_strlen($game->right) - mb_strlen($game->wrong));
                            $member->glory = $member->glory + $x;
                            $member->save();
                            $response->message = "Вы угадали слово!" . PHP_EOL . "Слово: {$result}" . PHP_EOL . "Клан {$member->title} получил {$x} славы.";
                        } else {
                            $response->message = "Вы угадали слово!" . PHP_EOL . "Слово: {$result}" ;
                        }
                    } else
                        $response->message = "Слово: {$result}" . PHP_EOL . "Угадали: {$game->right}" . PHP_EOL . "Не угадали: {$game->wrong}" . PHP_EOL . "Жизни: " . (mb_strlen($game->wrong)) . '/5';
                }
            } else {
                if (strlen($game->wrong) == 10) {
                    Game::updateGameStatus('виселица', $this->peer->id, 0);
                    $member = Clan::findClanByMember($this->user->id);
                    if ($member != false) {
                        $member->glory = $member->glory - 2;
                        $member->save();
                        $response->message = "Вы проиграли :(" . PHP_EOL . "Слово: {$game->need_word}" . PHP_EOL . "Клан {$member->title} потерял 2 славы.";
                    } else {
                        $response->message = "Вы проиграли :(" . PHP_EOL . "Слово: {$game->need_word}";
                    }
                    $game->need_word = '';
                    $game->save();
                } else {
                    if (stristr($game->right, $user_text) || stristr($game->wrong, $user_text)) {
                        $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                        $response->message = "Это буква уже вводилась: {$user_text}" . PHP_EOL . "Слово: {$result}" . PHP_EOL . "Угадали: {$game->right}" . PHP_EOL . "Не угадали: {$game->wrong}" . PHP_EOL . "Жизни: " . (mb_strlen($game->wrong)) . "/5";
                    } else {
                        $game->wrong .= $user_text;
                        $game->save();
                        $result = preg_replace("~[^ " . $game->right . "]~u", '_ ', $game->need_word);
                        $response->message = "Слово: {$result}" . PHP_EOL . "Угадали: {$game->right}" . PHP_EOL . "Не угадали: {$game->wrong}" . PHP_EOL . "Жизни: " . (mb_strlen($game->wrong))  . "/5";
                    }
                }
            }
        } else {
            $response->message = "";
        }
        return $response;
    }

    public function NeedWordAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame('виселица', $this->peer->id);
        if ($game->checker == 1) {
            if ($user_text == $game->need_word) {
                if (mb_strlen($game->right) <= (mb_strlen($game->need_word) - 4)) {
                    $game->checker = 0;
                    $game->wrong = null;
                    $game->right = null;
                    $game->save();
                    $member = Clan::findClanByMember($this->user->id);
                    if ($member != false) {
                        $member->glory = $member->glory + 10;
                        $member->save();
                        $response->message = "Вы угадали слово!" . PHP_EOL . "Слово: {$game->need_word}" . PHP_EOL . "Клан {$member->title} получил 10 славы.";
                    } else
                        $response->message = "Вы угадали слово!" . PHP_EOL . "Слово: {$game->need_word}";
                } else
                    $response->message = "Угадано слишком много букв :) Продолжай вводить буквы, хитрец :З";
            } else {
                $game->checker = 0;
                $game->wrong = null;
                $game->right = null;
                $game->save();
                $member = Clan::findClanByMember($this->user->id);
                if ($member != false) {
                    $member->glory = $member->glory - 5;
                    $member->save();
                    $response->message = "Вы не угадали загаднное слово: {$game->need_word}" . PHP_EOL . "Клан {$member->title} потерял 5 славы.";
                } else
                    $response->message = "Вы не угадали загаднное слово: {$game->need_word}";
            }
        } else {
            $response->message = "Игра ещё не запущена. Напишите Игра старт 'название игры'";
        }
        return $response;
    }

//    public function addWordsAction($user_text)
//    {
//        $response = new Response();
//        $response->peer_id = $this->peer->id;
//        if ($this->user->is_dev == 1) {
//            $delimiter = ', ';
//            $arr = explode (  $delimiter ,  $user_text);
//            foreach ($arr as $item) {
//                $word = new Words();
//                $word->word = $item;
//                $word->save();
//            }
//            $response->message = "Слова были добавлены";
//        }
//        return $response;
//    }

    /**
     *
     * Дуэль (надо тестить)
     *
     */

    public function DuelAction($object, $user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $word = "-";
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
        }
        $id = $this->getIdFromMessage($object);
        if (strpos($id, $word) !== false) {
            $response->message = "";
        } elseif ($id == $this->user->id) {
            $response->message = "";
        } elseif (isset($object['message']['reply_message'])) {
            $game = Game::findGame('дуэль', $this->peer->id);
            if ($game != false) {
                if ($game->checker > 19) {
                    $game->wrong = $this->user->id;
                    $game->right = $id;
                    $game->save();
                    $users = User::findById($id);
                    $this->vk->messagesSend($this->peer->id, "Жду подтверждения на дуэль от: " . $users->getName() . PHP_EOL . "У вас есть 20 секунд на то чтоб дать ответ." . PHP_EOL . "'Махыч' или 'Сцу'");
                    sleep(20);
                    $game = Game::findGame('дуэль', $this->peer->id);
                    if ($game->numb_shoot == 0) {
                        Game::updateGameStatus('дуэль', $this->peer->id, 20);
                        $this->vk->messagesSend($this->peer->id, $users->getName() . " сбежал с дуэли, поджав свой хвостик.");
                    }
                } else
                    $response->message = "Дуэль уже идёт." . PHP_EOL . "[id{$game->right}|стрелок 1] и [id{$game->wrong}|Стрелок 2]";
            }
        } elseif ($user->id != $this->user->id) {
            $game = Game::findGame('дуэль', $this->peer->id);
            if ($game != false  ) {
                if ($user->isExists()) {
                    if ($game->checker > 19) {
                        $game->wrong = $this->user->id;
                        $game->right = $user->id;
                        $game->save();
                        $users = User::findById($user->id);
                        $this->vk->messagesSend($this->peer->id, "Жду подтверждения на дуэль от: " . $users->getName() . PHP_EOL . "У вас есть 20 секунд на то чтоб дать ответ." . PHP_EOL . "'Махыч' или 'Сцу'");
                        sleep(20);
                        $game = Game::findGame('дуэль', $this->peer->id);
                        if ($game->numb_shoot == 0) {
                            Game::updateGameStatus('дуэль', $this->peer->id, 20);
                            $this->vk->messagesSend($this->peer->id, $users->getName() . " сбежал с дуэли, поджав свой хвостик.");
                        }
                    } else $response->message =  "Дуэль уже идёт." . PHP_EOL . "[id{$game->right}|стрелок 1] и [id{$game->wrong}|Стрелок 2]";
                }
            }
        }
        return $response;
    }

    public function duelGoAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame('дуэль', $this->peer->id);
        if ($game != false && $this->user->id == $game->right) {
            $game->numb_shoot = rand(3,6);
            $game->checker = 0;
            $game->save();
            $rand = rand(1,2);
            if($rand == 1) {
                $game->need_word = $game->wrong;
                $game->save();
                $response->message = "Первым стреляет: [id{$game->need_word}|Неуклюжий Сэм]";
                $response->setButtonRow(['Выстрелить', "shoot"], ['Выстрел в воздух', "shootInAir"]);
            } else {
                $game->need_word = $game->right;
                $game->save();
                $response->message = "Первым стреляет: [id{$game->need_word}|Косой Джо]";
                $response->setButtonRow(['Выстрелить', "shoot"], ['Выстрел в воздух', "shootInAir"]);
            }
        }
        return $response;
    }

    public function duelFalseAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame('дуэль', $this->peer->id);
        $users = User::findById($this->user->id);
        if ($game->checker == 20 && isset($game->right)) {
            Game::updateGameStatus('дуэль', $this->peer->id, 20);
            $response->message = $users->getName() . " сбежал с дуэли, поджав свой хвостик.";
        }
        return $response;
    }

    public function shootInAirAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame('дуэль', $this->peer->id);
        if ($game->checker != 20 && ($this->user->id == $game->wrong || $this->user->id == $game->right)) {
            if ($game->need_word == $this->user->id) {
                if ($game->checker <= $game->numb_shoot) {
                    if ($game->need_word == $game->right) {
                        $game->need_word = $game->wrong;
                        $game->save();
                    } else {
                        $game->need_word = $game->right;
                        $game->save();
                    }
                    $user = User::findById($game->need_word);
                    $response->message = "Очередь выстрела за " . $user->getName();
                    $response->setButtonRow(['Выстрелить', "Shoot"], ['Выстрел в воздух', "shootInAir"]);
                }
            } else $response->message = "Не ваша очередь стрелять!";
        }
        return $response;
    }

    public function shootAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $game = Game::findGame('дуэль', $this->peer->id);
        if ($game->checker != 20 && ($this->user->id == $game->wrong || $this->user->id == $game->right)) {
            if ($game->need_word == $this->user->id) {
                if ($game->checker == $game->numb_shoot + 1) {
                    $user = User::findById($game->need_word);
                    $game->need_word = null;
                    $game->wrong = null;
                    $game->right = null;
                    $game->checker = 20;
                    $game->numb_shoot = 0;
                    $game->save();
                    $member = Clan::findClanByMember($game->need_word);
                    if ($member->isExists()) {
                        $member->glory = $member->glory + 2;
                        $member->save();
                        $response->message = "Дуэль окончена." . PHP_EOL . " Победил: " . $user->getName() . PHP_EOL . "Клан {$member->title} Получил 2 славы.";
                    } else {
                        $response->message = "Дуэль окончена." . PHP_EOL . " Победил: " . $user->getName();
                    }
                } else {
                    $game->checker = $game->checker +1;
                    $game->save();
                    if ($game->need_word == $game->right) {
                        $game->need_word = $game->wrong;
                        $game->save();
                    } else {
                        $game->need_word = $game->right;
                        $game->save();
                    }
                    $user = User::findById($game->need_word);
                    $response->message = "Очередь выстрела за " . $user->getName();
                    $response->setButtonRow(['Выстрелить', "Shoot"], ['Выстрелить в воздух', "shootInAir"]);
                }
            } else $response->message = "Не ваша очередь стрелять!";
        }
        return $response;
    }

}