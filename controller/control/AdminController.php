<?php

namespace controller\control;

use comboModel\UserPeer;
use core\App;
use core\Controller;
use model\Role;
use model\User;
use model\Warning;
use model\Web;
use core\Response;

class AdminController extends Controller
{
    public function editRoleAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->peer->owner_id != $this->user->id) {
            $response->message = 'Вы не создатель беседы!';
            return $response;
        }
        $user = $this->getUserFromMessage($username, $object);
        if ($user === false) {
            $response->message = 'Я не знаю данного пользователя';
            return $response;
        }
        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
        if ($userPeer === false) {
            $response->message = 'Я не знаю данного пользователя';
            return $response;
        }
        $mainRole = Role::findById($userPeer->role_id);
        $response->message = "Роль данного пользователя: $mainRole->title" . PHP_EOL . PHP_EOL;
        $response->message .= "Изменить роль данному пользователю:";
        $roles = Role::findAllToChange($this->peer->owner_id);
        foreach ($roles as $role) {
            if ($mainRole->id != $role['id']) {
                $response->setButtonRow(["Изменить роль на {$role['title']}", "edit_user_role {$role['id']} $userPeer->user_id"]);
            }
        }
        return $response;
    }

    public function changeUserRoleAction($role_id, $user_id)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $role = Role::findById(intval($role_id));
        if ($role === false) {
            $response->message = 'Роль не найдена, скорее всего вы ее удалили';
            return $response;
        }
        $userPeer = UserPeer::findsByPeerAndUser(intval($user_id), $this->peer->id);
        if ($userPeer === false) {
            $response->message = 'Пользователь не найден';
            return $response;
        }
        $userPeer->role_id = $role->id;
        $userPeer->save();
        $response->message = 'Роль пользователя изменена!';
        return $response;
    }

    public function MutePeerAction($time): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->haveAccess(Role::MUTE_ACCESS)) {
            if ($this->peer->MutePeer == 0) {
                if ($time > 0) {
                    $this->peer->MutePeer = 1;
//                        $this->peer->TimeMute = time(); //Добавь в бд это поле
                    $this->peer->save();
                    $second = $time % 60;
                    $minutes = floor($time / 60);
                    $hours = floor($time / 3600);
                    $minute = $minutes - $hours * 60;
                    if ($time < 60)
                        $this->vk->messagesSend($this->peer->id, "В беседе объявлен тихий час на $second cекунд. Лишь имеющие доступ могут общаться. Остальные будут кикнуты за любое сообщение....");
                    if ($time >= 60 && $time < 3600)
                        $this->vk->messagesSend($this->peer->id, "В беседе объявлен тихий час на $minutes минут $second секунд. Лишь имеющие доступ могут общаться. Остальные будут кикнуты за любое сообщение....");
                    elseif ($time >= 3600)
                        $this->vk->messagesSend($this->peer->id, "В беседе объявлен тихий час на $hours часов $minute минут $second секунд. Лишь имеющие доступ могут общаться. Остальные будут кикнуты за любое сообщение....");
//                    $this->userPeer->createCallback("В беседе снят тихий час. Все участники снова могут общаться.", $this->peer->id, time() + intval($user_text));
                    $this->userPeer->createCallback('unmutePeer', time() + intval($time));
                } else
                    $response->message = "Отриацательные значения или 0 нельзя. На сколько секунд объявить тишину в беседе?"
                        . PHP_EOL . "1 минута = 60"
                        . PHP_EOL . "10 минут = 600"
                        . PHP_EOL . "1 час = 3600";
            } else
                $response->message = "В беседе итак объявлен тихий чай. Он будет снят через %Value%"; //Добавь в бдшку поле выше
        } else
            $response->message = "Вы не имеете доступ к данной команде.";
        return $response;
    }

    public function MutePeerRemoveAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->haveAccess(Role::MUTE_ACCESS)) {
            if ($this->peer->MutePeer == 1 ) {
                $this->peer->MutePeer = 0;
                $this->peer->save();
                $response->message = "В беседе снят тихий час. Все участники снова могут общаться.";
            } else
                $response->message= "В беседе нет мута, можно свободно общаться.";
        } else
            $response->message = "Вы не имеете доступ к данной команде.";
        return $response;
    }

    public function findAdminsByWebAction(): Response //TODO: нахуя?
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $web = new Web($this->peer->web_id);
        $peers = $web->getPeersIds();
        $needs = [];
        $result = [];
        $allUser = [];
        $users = UserPeer::SelectUsers($this->peer->id);
        if ($this->user->is_dev == 1 || $web->owner_id == $this->user->id) {
            foreach ($peers as $peer) {
                foreach ($users as $item) {
                    array_push($allUser, $item['user_id']);
                }
                $number = 0;
                while ($number != count($result)) {
                    $user = new User($result[$number]);
                    if (!in_array($user->id, $allUser)) {
                        if (!in_array($user->id, $needs)) {
                            array_push($needs, $user->id);
                        }
                    }
                    $number++;
                }
            }
            if (count($needs) != 0) {
                $message = $this->render('admin/needs_admin', [
                    'admins' => $needs,
                    'allCount' => count($result),
                    'count' => count($needs)
                ]);
                $response->message = $message;
            } else
                $response->message = "Все админы сетки уже в беседе.";
        } else
            $response->message = "Вы не создатель данной сетки.";
        return $response;
    }

    public function AdministrationAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'id - человек(сообщение или ссылка или домен), всё пишется без скобок.'
                . PHP_EOL . '😈😈Администрирование'
                . PHP_EOL . '1. Бан [id]'
                . PHP_EOL . '2. -Бан [id]'
                . PHP_EOL . '3. Кик [id]'
                . PHP_EOL . '4. Пред [id]'
                . PHP_EOL . '5. -Пред [id]'
                . PHP_EOL . '6. Техподдержка'
                . PHP_EOL . '7. Админ статус [id]'
                . PHP_EOL . '8. Преды'
                . PHP_EOL . '9. Неактив'
                . PHP_EOL . '10. Админы беседы'
                . PHP_EOL . '11. Молчуны'
                . PHP_EOL . '12. Название статуса [номер] [text]'
                . PHP_EOL . '13. Название статусов'
                . PHP_EOL . '14. Понизить [id]'
                . PHP_EOL . '15. Повысить [id]'
                . PHP_EOL . '16. Разжаловать'
                . PHP_EOL . '17. Правила/приветствие'
                . PHP_EOL . '18. Правила/приветствие удалить'
                . PHP_EOL . '19. Правила/приветствие установить [text]';
            $response->setButtonRow(['Бан со статуса', '1'], ['Кик со статуса', '2']);
            $response->setButtonRow(['Пред со статуса', '3'], ['Кол-во предов', '4']);
            $response->setButtonRow(['Назад', '0']);
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function ProfileAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'id - человек(сообщение или ссылка или домен), text - слово или фраза, | - или'
                . PHP_EOL . '❤❤Работа со своим профилем'
                . PHP_EOL . '1. Ник [text]'
                . PHP_EOL . '2. Значок  [эмоджи]'
                . PHP_EOL . '3. Профиль [id] | мой';
            $response->setButton('Назад', '0');
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function PeerAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'id - айди настройки, состояние - включено/выключено'
                . PHP_EOL . '✉✉Работа с беседой✉✉'
                . PHP_EOL . '1. Беседа инициализация'
                . PHP_EOL . '2. Беседа обновить'
                . PHP_EOL . '3. Беседа настройки'
                . PHP_EOL . '4. Беседа настройка [id] [состояние]'
                . PHP_EOL . '5. Чатссылка'
                . PHP_EOL . '6. Кик собак'
                . PHP_EOL . '7. Кик вышедших'
                . PHP_EOL . '8. Кик неактив Х, где Х - дни'
                . PHP_EOL . '9. +автокик или -автокик'
                . PHP_EOL . '10. Беседа инфо'
                . PHP_EOL . '11. Приветствие'
                . PHP_EOL . '12. Правила'
                . PHP_EOL . '13. Беседа мут [число]'
                . PHP_EOL . '14. Беседа -мут';
            $response->setButtonRow(['Беседа инициализация', '1'], ['Беседа обновить', '2']);
            $response->setButtonRow(['Беседа настройки', '3'], ['Чатссылка', '4']);
            $response->setButtonRow(['Кик собачек', '5'], ['Кик неактив', '6']);
            $response->setButtonRow(['+автокик', '7'], ['-автокик', '8']);
            $response->setButtonRow(['Назад', '0']);
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function WebAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'id - айди сетки, text - слово или фраза, состояние - включено/выключено'
                . PHP_EOL . '📶📶Работа с сеткой бесед'
                . PHP_EOL . '1. Сетка список'
                . PHP_EOL . '2. Сетка создать [text]'
                . PHP_EOL . '3. Сетка текущая'
                . PHP_EOL . '4. Сетка настройки'
                . PHP_EOL . '5. Сетка настройка [id] [состояние]'
                . PHP_EOL . '6. Сетка топ'
                . PHP_EOL . '7. Сетка топ дня/недели'
                . PHP_EOL . '8. Сетка топ бесед'
                . PHP_EOL . '9. Сетка топ бесед дня/недели'
                . PHP_EOL . '10. Сетка инфо'
                . PHP_EOL . '11. Сетка удалить [id]';
            $response->setButtonRow(['Сетка список', '1'], ['Сетка текущая', '2']);
            $response->setButtonRow(['Сетка настройки', '3'], ['Сетка удалить', '4']);
            $response->setButtonRow(['Назад', '0']);
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function CommandsAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'text - слово или фраза, переслать - сообщение пользователя с неким текстом/вложением'
                . PHP_EOL . '😒😒Полезные команды'
                . PHP_EOL . '1. Действия'
                . PHP_EOL . '2. Бот увед'
                . PHP_EOL . '3. Бот -увед'
                . PHP_EOL . '4. Онлайн'
                . PHP_EOL . '5. Брак'
                . PHP_EOL . '6. -Брак'
                . PHP_EOL . '7. Мои дети'
                . PHP_EOL . '8. Мои родители'
                . PHP_EOL . '9. Мой брак'
                . PHP_EOL . '10. Усыновить'
                . PHP_EOL . '11. Удочерить'
                . PHP_EOL . '12. Зал славы'
                . PHP_EOL . '13. Триггер создать [text] (переслать)'
                . PHP_EOL . '14. Триггер удалить [text]'
                . PHP_EOL . '15. Триггеры'
                . PHP_EOL . '16. Новости'
                . PHP_EOL . '17. Найди {text}'
                . PHP_EOL . '18. Биржа'
                . PHP_EOL . '19. Топ (уровней, дня, недели)'
                . PHP_EOL . '20. Шипперим'
                . PHP_EOL . '21. Ники';
            $response->setButton('Назад', '0');
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function RpCommandsAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = "Некоторые рп команды"
                . PHP_EOL . '💘💘Список команд с кем-то: '
                . PHP_EOL . 'Арестовать'
                . PHP_EOL . 'Воскресить'
                . PHP_EOL . 'Взять'
                . PHP_EOL . 'Задушить'
                . PHP_EOL . 'и др....'
                . PHP_EOL . '💔💔Список соло команд:'
                . PHP_EOL . 'Выпить'
                . PHP_EOL . 'Воскреснуть'
                . PHP_EOL . 'Загрустить'
                . PHP_EOL . 'Одеться'
                . PHP_EOL . 'и др....';
            $response->setButton('Назад', '0');
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function ClansAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'id - человек(сообщение или ссылка или домен), text - слово или фраза'
                . PHP_EOL . "Модуль кланы"
                . PHP_EOL . '1. Создать клан [text]'
                . PHP_EOL . '2. Удалить клан'
                . PHP_EOL . '3. Пригласить в клан [id]'
                . PHP_EOL . '4. Выйти из клана'
                . PHP_EOL . '5. Участники клана'
                . PHP_EOL . '6. Клан значок [эмоджи]'
                . PHP_EOL . '7. Клан кик [id]'
                . PHP_EOL . '8. Клан мой'
                . PHP_EOL . '9. Топ кланов'
                . PHP_EOL . '10. Клан название [text]';
            $response->setButton('Назад', '0');
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function GamesAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = 'id - человек (ответ или ссылка или домен)'
                . PHP_EOL . "Текущие игры на данный момент:"
                . PHP_EOL . "1. Виселица - бот загадывает слово на определённую тему и даёт вам 5 попыток на то чтоб угадать его."
                . PHP_EOL . "1.1. Игра старт виселица"
                . PHP_EOL . "1.2. Игра виселица 'буква, например а'"
                . PHP_EOL . "1.3. Слово 'нужное слово'"
                . PHP_EOL . "1.4. Игра стоп виселица"
                . PHP_EOL . "2. Дуэль [id] - Каждый игрок стреляет по очереди и бот считает выстрелы, пока кого-то не убьют."
                . PHP_EOL . "2.1. Выстрелить - +n% к шансу того что человек убьёт следующим выстрелом"
                . PHP_EOL . "2.2. Выстрелить в воздух - -n% к шансу того что человек убьёт следующим выстрелом"
                . PHP_EOL . "P.S Чтобы их подключить обратитесь к [id91737880|Разработчику бота].";
            $response->setButton('Назад', '0');
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function ModuleAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $response->message = "Может быть вы имели один из следующих модулей? Нажимайте на кнопочки."
                . PHP_EOL . "1) Админка"
                . PHP_EOL . "2) Участник"
                . PHP_EOL . "3) Беседа"
                . PHP_EOL . "4) Сетка"
                . PHP_EOL . "5) Команды"
                . PHP_EOL . "6) Рп команды (действия)"
                . PHP_EOL . "7) Кланы"
                . PHP_EOL . "8) Игры"
                . PHP_EOL . "Eсли есть вопросы по функционалу бота или имеются ошибки в работе бота можно и нужно написать им: "
                . PHP_EOL . "1. [hironori|Николай] (Отвечу всем, team lead bot developer)"
                . PHP_EOL . "2. [eoremic|Антон] (В сети почти всегда, middle bot developer)";
            $response->setButtonRow(['Администрирование', '1'], ['Участник', '2']);
            $response->setButtonRow(['Беседа', '3'], ['Сетка', '4']);
            $response->setButtonRow(['Команды', '5'], ['Действия', '6']);
            $response->setButtonRow(['Кланы', '7'], ['Игры', '8']);
        } else
            $response->message = "К сожалению у вас нет доступа к данной команде, для вывода всех команд пропишите Помощь.";
        return $response;
    }

    public function TotalAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        if ($this->user->is_dev == 1) {
            if ($user !== false) {
                $user->black_list = 1;
                $user->save();
                $this->vk->messagesSend($this->peer->id, "[id$user->id|$user->first_name_nom] получил ТоталЧс. Бот будет игнорировать все его команды.");
            } else
                $response->message = "Выберите сообщение человека, кому хотите выдать тотал.";
        }
        return $response;
    }

    public function RemoveTotalAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        if ($this->user->is_dev == 1) {
            if ($user !== false) {
                $user->black_list = 0;
                $user->save();
                $this->vk->messagesSend($this->peer->id, 'У пользователя снят ТоталЧс.');
            }
        }
        return $response;
    }

    public function warningAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
        if ($user->id == $this->user->id) {
            $response->message = "Нельзя выдать пред самому себе.";
            return $response;
        }
        if ($user->is_dev == 1) {
            $response->message = "Не могу выдать предупреждение разработчику бота.";
            return $response;
        }
        if ($userPeer->haveAccess(Role::IMMUNE_ACCESS)) {
            $response->message = "Не могу выдать предупреждение пользователю, имеющему иммунитет к командам.";
            return $response;
        }
        if ($this->userPeer->haveAccess(Role::PRED_ACCESS) || ($this->user->is_dev == 1)) {
            if ($user !== false) {
                $warning = new Warning();
                $warning->peer_id = $userPeer->peer_id;
                $warning->user_id = $userPeer->user_id;
                $warning->tst = time();
                $id = $warning->save();
                $numberWarn = Warning::getWarnings($userPeer);
                if ($numberWarn >= $this->peer->getSetting(3)) {
                    Warning::clear($userPeer);
                    $result = $this->vk->messagesRemoveChatUser($this->peer->id, $userPeer->user_id);
                    if (isset($result['response']) && $result['response'] == 1) {
                        $response->message = "Пользователь удален из беседы, получив $numberWarn предупреждений из {$this->peer->getSetting(3)}.";
                        $this->removeUserFromPeer($userPeer);
                    }
                    elseif (isset($result['error']) && $result['error']['error_code'] == 15) {
                        $response->message = 'Не могу выгнать админа';
                    }
                } else {
                    if ($id) {
                        $NumberWarn = $this->peer->getSetting(3);
                        $warn = Warning::getWarnings($userPeer);
                        $response->message = "Пользователь получил $warn/$NumberWarn!";
                    } else
                        $response->message = 'Ошибка!';
                }
            }
        } else
            $response->message = "Ваша роль не имеет доступа к данной команде";
        return $response;
    }

    public function banAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        $group = $this->getGroupFromMessage($username, $object);
        if ($user->is_dev == 1) {
            $response->message = "Не могу забанить СОЗДАТЕЛЯ БОТА!";
            return $response;
        }
        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
        if ($userPeer->haveAccess(Role::IMMUNE_ACCESS)) {
            $response->message = "Не могу забанить пользователя, имеющего иммунитет к командам!";
            return $response;
        }
        if ($this->userPeer->haveAccess(Role::BAN_ACCESS) || $this->user->is_dev == 1) {
            if ($user !== false) {
                if ($user->isExists()) {
                    $this->userPeer->ban_by_peer = $this->userPeer->ban_by_peer + 1;
                    $this->userPeer->save();
                    $userPeer->deleted = 1;
                    $userPeer->have_ban = 1;
                    $userPeer->save();
                    $this->peer->users_count = $this->peer->users_count - 1;
                    $this->peer->count_kick = $this->peer->count_kick + 1;
                    $this->peer->save();
                    $this->vk->messagesSend($this->peer->id, 'Пользователь забанен в беседе!');
                    $result = $this->vk->messagesRemoveChatUser($this->peer->id, $userPeer->user_id);
                    if (isset($result['error']) && $result['error']['error_code'] == 15) {
                        $response->message = 'Не могу забанить админа';
                    }
                }
            } elseif ($group !== false) {
                if (!$group->isAdmin($this->peer->id)) {
                    $group->setDeleted($this->peer->id);
                    $group->setBan($this->peer->id);
                    $result = $this->vk->messagesRemoveChatUser($this->peer->id, $group->id);
                    if (isset($result['error']) && $result['error']['error_code'] == 15)
                        $response->message = 'Не могу забанить админа';
                    else
                        $response->message = 'Группа забанена в беседе!';
                } else
                    $response->message = "Не могу забанить группу администратора";
            }
        } else
            $response->message = "Вам не доступна данная команда!";
        return $response;
    }

    public function kickAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        $group = $this->getGroupFromMessage($username, $object);
        if ($user->is_dev == 1) {
            $response->message = "Не могу выгнать СОЗДАТЕЛЯ БОТА!";
            return $response;
        }
        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
        if ($userPeer->haveAccess(Role::IMMUNE_ACCESS)) {
            $response->message = "Не могу выгнать пользователя, имеющего иммунитет к командам!";
            return $response;
        }
        if ($this->userPeer->haveAccess(Role::KICK_ACCESS) || $this->user->is_dev == 1) {
            if ($user !== false) {
                $this->removeUserFromPeer($userPeer);
                $this->vk->messagesSend($this->peer->id, 'Пользователь удален из беседы!');
                $result = $this->vk->messagesRemoveChatUser($this->peer->id, $userPeer->user_id);
                if (isset($result['error']) && $result['error']['error_code'] == 15) {
                    $response->message = 'Не могу выгнать админа';
                }
            } elseif ($group !== false) {
                if (!$group->isAdmin($this->peer->id)) {
                    $group->setDeleted($this->peer->id);
                    $result = $this->vk->messagesRemoveChatUser($this->peer->id, $group->id);
                    if (isset($result['error']) && $result['error']['error_code'] == 15)
                        $response->message = 'Не могу удалить админа';
                    else
                        $response->message = 'Группа удалена из беседы!';
                } else
                    $response->message = "Не могу удалить группу администратора";
            }
        } else
            $response->message = "Вам не доступна данная команда!";
        return $response;
    }

    public function getOnlineAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $response->message = "Пользователи были привлечены к вниманию, @online" . PHP_EOL;
        return $response;
    }

    public function muteUserAction($time, $username, $object): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $time_to_mute = $time;
        $user = $this->getUserFromMessage($username, $object);
        if ($user->is_dev == 1) {
            $response->message = "Не могу заглушить СОЗДАТЕЛЯ БОТА!";
            return $response;
        }
        if($this->user->id == $user->id) {
            $response->message = "Вы пытаетесь заглушить самого себя!";
            return $response;
        }
        $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
        if ($userPeer->haveAccess(Role::IMMUNE_ACCESS)) {
            $response->message = "Не могу заглушить пользователя, имеющего иммунитет к командам!";
            return $response;
        }
        if ($this->userPeer->haveAccess(Role::MUTE_ACCESS) || $this->user->is_dev == 1) {
            if ($user !== false) {
                if ($time_to_mute > 0) {
                    if ($this->user->id != $userPeer->user_id) {
                        $userPeer->muted = time() + $time_to_mute;
                        $userPeer->save();
                        $secund = $time_to_mute % 60;
                        $minutes = floor($time_to_mute / 60);
                        $hours = floor($time_to_mute / 3600);
                        $minut = $minutes - $hours * 60;
                        if ($time_to_mute < 60)
                            $this->vk->messagesSend($this->peer->id, "Пользователь заглушен в беседе на $secund cекунд. Если он напишет сообщение, будет удален из беседы.");
                        if ($time_to_mute >= 60 && $time_to_mute < 3600)
                            $this->vk->messagesSend($this->peer->id, "Пользователь заглушен в беседе на $minutes минут $secund секунд. Если он напишет сообщение, будет удален из беседы.");
                        elseif ($time_to_mute >= 3600)
                            $this->vk->messagesSend($this->peer->id, "Пользователь заглушен в беседе на $hours часов $minut минут $secund секунд. Если он напишет сообщение, будет удален из беседы.");
//                            $this->user->createNotification("[id{$userPeer->user_id}|Пользователь] был размучен по истечению срока. Впредь не хулиганьте.", $this->peer->id, time() + intval($user_text));
                        $this->userPeer->createCallback('unmuteUser', time() + intval($time_to_mute), ['user_id' => $userPeer->user_id]);
                    }
                } else
                    $response->message = "Отрицательные значения или 0 нельзя. Выберите сообщение человека и введите на сколько секунд его замутить"
                        . PHP_EOL . "1 час = 3600"
                        . PHP_EOL . "1 день = 86400"
                        . PHP_EOL . "7 дней = 604800";
            }
        } else
            $response->message = "Ваш статус не позволяет пользоваться данной командой. Необходимый статус {$this->peer->getSetting(3)}";
        return $response;
    }

    public function removeMuteUserAction($username, $object): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        if($this->user->id == $user->id) {
            $response->message = "Нельзя убрать мут с самого себя!";
            return $response;
        }
        if ($this->userPeer->haveAccess(Role::MUTE_ACCESS) || $this->user->is_dev == 1) {
            if ($user !== false) {
                $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
                $userPeer->muted = 0;
                $userPeer->save();
                $this->vk->messagesSend($this->peer->id, 'У пользователя убран мут, он может смело писать');
            }
        } else {
            $response->message = "Вам не доступна данная команда!";
        }
        return $response;
    }

    public function removeWarningAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        if ($user->id == $this->user->id) {
            $response->message = 'Вы не можете снять предупреждение с себя! Не будьте букой.';
            return $response;
        }
        if ($this->userPeer->haveAccess(Role::PRED_ACCESS) || ($this->user->is_dev == 1)) {
            $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
            $warning = Warning::GetWarningId($user->id, $this->peer->id);
            if ($warning->isExists()) {
                $warning->delete();
                $warn = Warning::getWarnings($userPeer);
                $is = $warn + 1;
                $response->message = "Снято 1 предупреждение из $is. Осталось $warn.";
            } else
                $response->message = 'У пользователя нет предупреждений.';
        } else
            $response->message = "Вам не доступна данная команда!";
        return $response;
    }

    public function allWarningAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $warnings = Warning::getAllWarning($this->peer->id);
        if (!empty($warnings)) {
            $message = $this->render('top/warnings', [
                'warnings' => $warnings,
                'title' => 'Преды в данной беседе:'
            ]);
            $response->message = $message;
        } else
            $response->message = "Увы, но в данной беседе нет предов.";
        return $response;
    }

    public function removeBanAction($object, $username): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($username, $object);
        $group = $this->getGroupFromMessage($username, $object);
        if ($this->userPeer->haveAccess(Role::BAN_ACCESS) || ($this->user->is_dev == 1)) {
            if ($user !== false) {
                $userPeer = UserPeer::findsByPeerAndUser($user->id, $this->peer->id);
                if (isset($userPeer)) {
                    $userPeer->have_ban = 0;
                    $userPeer->save();
                    $response->message = 'Пользователь разбанен';
                }
            } elseif ($group !== false) {
                if ($this->peer->getSetting(17) == 1) {
                    $group->unsetBan($this->peer->id);
                    $response->message = 'Группа разбанена';
                }
            }
        } else {
            $response->message = "Вам не доступна данная команда!";
        }
        return $response;
    }

    public function ChatUrlAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->role_id == Role::MAIN_ADMIN) {
            $result = $this->vk->messagesGetInviteLink($this->peer->id);
            if ($result['response']['link']) {
                $response->message = $result['response']['link'];
                $response->setButton('Чатссылка удалить', '1');
            } elseif ($this->peer->url == '') {
                $response->message = "Добавьте чатссылку к чату.";
            } else {
                $response->message = $this->peer->url;
                $response->setButton('Чатссылка удалить', '1');
            }
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }


    public function SetChatUrlAction($url): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->role_id == Role::MAIN_ADMIN) {
            if (preg_match('/(https|http):\/\/vk\.me\/join/', $url)) {
                $this->peer->url = $url;
                $this->peer->save();
                $response->message = "Чатссылка установлена.";
            } else
                $response->message = "Ввведите ссылку на чат.";
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function DeleteChatUrlAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->role_id == Role::MAIN_ADMIN) {
            $this->peer->url = null;
            $this->peer->save();
            $response->message = "Чатссылка удалена.";
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function sleepersAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || ($this->user->is_dev == 1)) {
            $users = UserPeer::getSleepersUsers($this->peer->id);
            if (!empty($users)) {
                $message = $this->render('top/inactive', [
                    'userInfo' => $users,
                    'title' => 'Спящие пользователи:',
                    'timeInactive' => 0
                ]);
                $response->message = $message;
            } else
                $response->message = "Все пользователи активничали! Так держать!";
        } else
            $response->message = "У вас нет доступа к данной команде.";
        return $response;
    }

    public function AddAdminAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
//        if ($this->user->is_dev == 1) {
//            $date = explode(" ", $user_text);
//            $ts = 2000000000 + $date[0];
//            $peer = new Peer($ts);
//            $text = $this->vk->messagesAddChatUser($ts, $this->user->id, '');
//            $response->message = "Вы были добавлены в беседу {$peer->title}";
//        }
//        $response->message = print_r($text,1);
        return $response;
    }

    public function KickDeactivatedAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->haveAccess(Role::KICK_ACCESS)) {
            $count = null;
            $result = $this->vk->messagesGetConversationMembers($this->peer->id);
            foreach ($result['response']['profiles'] as $user) {
                if (isset($user['deactivated']) && !is_null($user['deactivated'])) {
                    $users = UserPeer::findsByPeerAndUser($user['id'], $this->peer->id);
                    $users->deleted = 1;
                    $users->save();
                    $this->peer->users_count = $this->peer->users_count - 1;
                    $this->peer->count_kick = $this->peer->count_kick + 1;
                    $this->peer->save();
                    $count++;
                    $this->vk->messagesRemoveChatUser($this->peer->id, $users->user_id);
                }
            }
            if ($count != null)
                $this->vk->messagesSend($this->peer->id, "Все собачки были удалены!");
            else
                $this->vk->messagesSend($this->peer->id, "Некого удалять!");
        } else
            $response->message = "Вам не доступна данная команда!";
        return $response;
    }

    public function KickLeaversAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->user->is_dev == 1 || $this->userPeer->haveAccess(Role::KICK_ACCESS)) {
            $allUser = [];
            $items = [];
            $users = UserPeer::SelectUsers($this->peer->id);
            $result = $this->vk->messagesGetConversationMembers($this->peer->id);
            foreach ($users as $user) {
                array_push($allUser, $user['user_id']);
            }
            foreach ($result['response']['items'] as $item) {
                array_push($items, $item['member_id']);
            }
            $count = null;
            foreach ($allUser as $user) {
                if (!in_array($user, $items)) {
                    $member = UserPeer::findsByPeerAndUser($user, $this->peer->id);
                    $member->deleted = 1;
                    $member->save();
                    $this->peer->users_count = $this->peer->users_count - 1;
                    $this->peer->count_kick = $this->peer->count_kick + 1;
                    $this->peer->save();
                    $count++;
                    $this->vk->messagesSend($this->peer->id, print_r($user,1));
                    $this->vk->messagesRemoveChatUser($this->peer->id, $user);
                }
            }
            if ($count != null)
                $this->vk->messagesSend($this->peer->id, "Все вышедшие пользователи были кикнуты.");
            else
                $this->vk->messagesSend($this->peer->id, "Некого удалять!");
        } else
            $response->message = "Вам не доступна данная команда!";
        return $response;
    }

    public function ChatInfoAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev) {
            $days = floor((time() - $this->peer->days) / 86400);
            $count = count(UserPeer::getAdmins($this->peer->id));
            $color = "positive";
            if ($this->peer->getSetting(18) == 1)
                $color = "Зелёный";
            elseif ($this->peer->getSetting(18) == 2)
                $color = "Красный";
            elseif ($this->peer->getSetting(18) == 3)
                $color = "Белый";
            elseif ($this->peer->getSetting(18) == 4)
                $color = "Синий";
            $countLeave = abs($this->peer->users_count_old - $this->peer->users_count);
            $response->message = "Беседа {$this->peer->title}"
                . PHP_EOL . "Id беседы: " . ($this->peer->id - App::$peerStartNumber)
                . PHP_EOL . "Было участников: {$this->peer->users_count_old} Стало: {$this->peer->users_count}"
                . PHP_EOL . "Админов: $count"
                . PHP_EOL . "Беседе $days дней"
                . PHP_EOL . "Настройки беседы (0 выкл, 1 вкл)"
                . PHP_EOL . "Автокик: {$this->peer->getSetting(9)}"
                . PHP_EOL . "Ссылки: {$this->peer->getSetting(10)}"
                . PHP_EOL . "Ошибки чата: {$this->peer->getSetting(16)}"
                . PHP_EOL . "Цвет кнопок: $color"
                . PHP_EOL . "Количество киков за сегодня: {$this->peer->count_kick}"
                . PHP_EOL . "Вышли: $countLeave";
        } else {
            $response->message = "Вы не администратор данной беседы!";
        }
        return $response;
    }

    public function RulesSetAction($rules): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $this->peer->rules = $rules;
            $this->peer->save();
            $response->message = "Правила установлены.";
        } else
            $response->message = "Вы не являетесь администратором в данной беседе!";
        return $response;
    }

    public function RulesDeletedAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
                $this->peer->rules = null;
                $this->peer->save();
                $response->message = "Правила успешно удалены.";
            } else
                if ($this->peer->getSetting(16) == 1)
                    $response->message = "Вы не являетесь админов в данной беседе!";
        }
        return $response;
    }

    public function RulesAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '')
            if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1)
                if ($this->peer->rules != '')
                    $response->message = "Правила беседы: " . PHP_EOL . $this->peer->rules;
                else
                    $response->message = "Необходимо установить правила по команде правила установить [text].";
        return $response;
    }

    public function SetHelloMessageAction($hello_message): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
            $this->peer->HelloMessage = $hello_message;
            $this->peer->save();
            $response->message = "Приветствие установлено.";
        } else
            $response->message = "Вы не являетесь администратором в данной беседе!";
        return $response;
    }

    public function HelloMessageDeletedAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1) {
                $this->peer->HelloMessage = null;
                $this->peer->save();
                $response->message = "Приветствие успешно удалено.";
            } else
                $response->message = "Вы не являетесь администратором в данной беседе. Даётся через пк.";
        }
        return $response;
    }

    public function HelloMessageAction($user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '')
            if ($this->userPeer->role_id == Role::MAIN_ADMIN || $this->user->is_dev == 1)
                if ($this->peer->HelloMessage != '')
                    $response->message = "Приветствие беседы: " . PHP_EOL . $this->peer->HelloMessage;
                else
                    $response->message = "Необходимо установить приветствие по команде приветствие установить [text].";
        return $response;
    }

    public function BanListAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $message = 'Бан лист пуст.';
        if ($this->userPeer->haveAccess(Role::BAN_ACCESS) || $this->user->is_dev == 1) {
            $users = UserPeer::SelectIsBan($this->peer->id);
            if (count($users) != 0) {
                $message = $this->render('admin/ban_list', [
                    'peer_id' => $this->peer->id,
                    'title' => "Пользователи забаненные в беседе:",
                    'users' => $users
                ]);
            }
        }
        $response->message = $message;
        return $response;
    }

    public function invitedByAction($object, $user_text): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $user = $this->getUserFromMessage($user_text, $object);
        $id = $user->id;
        $members = $this->vk->messagesGetConversationMembers($this->peer->id)['response']['items'];
        $response->message = print_r($members, 1);
        foreach ($members as $member) {
            if ($member['member_id'] == $id) {
                $inviter = User::findById($member['invited_by']);
                if ($inviter !== false) {
                    $response->message = 'Пригласил' . (($inviter->sex == 1) ? 'а ' : ' ') . $inviter->getFullName();
                } else {
                    $response->message = 'Я не знаю пригласившего!';
                }
                return $response;
            }
        }
        $response->message = 'Пользователь не указан!';
        return $response;
    }

    /**
     * @param $userPeer
     */
    public function removeUserFromPeer($userPeer): void
    {
        $this->userPeer->kick_by_peer = $this->userPeer->kick_by_peer + 1;
        $this->userPeer->save();
        $userPeer->deleted = 1;
        $userPeer->save();
        $this->peer->users_count = $this->peer->users_count - 1;
        $this->peer->count_kick = $this->peer->count_kick + 1;
        $this->peer->save();
    }

    public function getAdminsAction(): Response
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $roles = $this->peer->getAdmins();
        $response->message = '';
        foreach ($roles as $role)
        {
            $response->message .= $role['title'].':'.PHP_EOL;
            foreach ($role['users'] as $user)
            {
                $u = User::findById($user['user_id']);
                $response->message .= $u->getFullName().PHP_EOL;
            }
        }
        return $response;
    }
}