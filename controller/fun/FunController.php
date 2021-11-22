<?php

namespace controller\fun;

use comboModel\UserPeer;
use Controller;
use model\Peer;
use model\User;
use Response;

class FunController extends Controller
{
    public function mansurAction($object)
    {
        return [
            'message' => 'Мансур Вайсберг молодец,
            Админ, царь и борец,
            Он народ здесь весь собрал,
            И он его ведь не предал',
            'peer_id' => $object['message']['peer_id']
        ];
    }

    public function hironoriAction($object)
    {
        return [
            'message' => 'Может быть Никита? Он же так похож на Никиту (как вы, блет, это путаете???)',
            'peer_id' => $object['message']['peer_id']
        ];
    }

    private function mb_ucfirst($str, $encoding = NULL)
    {
        if ($encoding === NULL) {
            $encoding = mb_internal_encoding();
        }

        return mb_substr(mb_strtoupper($str, $encoding), 0, 1, $encoding) . mb_substr($str, 1, mb_strlen($str) - 1, $encoding);
    }

    public function NewsAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        preg_match_all('/>[а-яА-Яa-zA-Z0-9«» -]{40,100}</u', file_get_contents("https://yandex.ru"), $result);
        $news = 'Главные новости на сегодня:'. PHP_EOL;
        $cicle = 0;
        foreach ($result[0] as $value) {
            $cicle++;
            $value = mb_substr($value, 1, mb_strlen($value) - 2, 'UTF-8');
            $news = $news . $cicle . '. ' . $value . "\r\n";
            if (mb_strlen($news) > 700) {
                break;
            }
        }
        $response->message = $news;
        return $response;
    }

    public function stockMarketAction()
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $url = "http://www.cbr.ru/scripts/XML_daily.asp"; // URL
        $curs = [];
        if (!$xml = simplexml_load_file($url)) $response->message = 'Ошибка загрузки XML'; // загружаем полученный документ в дерево XML
        $xml->attributes();
        $curs['date'] = strtotime($xml->attributes()->Date); // получаем текущую дату
        foreach ($xml->Valute as $m) {
            if ($m->CharCode == "USD" || $m->CharCode == "EUR") {
                $curs[(string)$m->CharCode] = (float)str_replace(",", ".", (string)$m->Value); // запись значений в массив
            }
        }
        $result = 'Курс рубля на сегодня' . "\r\n" . $curs["USD"] . ' - Доллар ' . "\r\n" . $curs["EUR"] . ' - Евро' . "\r\n";
        $response->message = $result;
        return $response;
    }

    public function WeatherAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        $header = [
            'accept: application/json, text/plain, */*',
            'accept-encoding: gzip, deflate, br',
            'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control: max-age=0',
            'Connection: keep-alive',
            'Cookie: user_city_id=21409; geoplugin_latitude=55.7527; geoplugin_longitude=37.6172; _gcl_au=1.1.1072698149.1592850451; _ga=GA1.2.658140146.1592850451; _gid=GA1.2.78152469.1592850451; _ym_uid=1592850451619027229; _ym_d=1592850451; _ym_isad=2; user_city_id=1; cpass=1; arns=1592854428798; advice-19072018=viewed; _gat_UA-3060375-6=1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36 OPR/68.0.3618.142'
        ];
        $res = $this->curlExec("https://sinoptik.com.ru/api/suggest.php?q=$user_text&l=ru&clat=55.75&clon=37.62", [], $header);
        print_r($res);
        $response->message = "Пните Колю чтоб сделал погоду!!!!!";
        return $response;
    }

    public function sendMesAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($this->userPeer->status >= $this->peer->getSetting(14) || $this->user->is_dev == 1) {
            $params = explode(" ", $user_text);
            $ts2 = $params[0] + \App::$peerStartNumber;
            $ts = $this->peer->id - \App::$peerStartNumber;
            $peer = new Peer($ts2);
            if(preg_match('/(https|http):\/\/vk\.com/', $user_text))
            {
                $response->message = 'Ссылка в тексте на вк, не гуд!';
                return $response;
            }
            if(preg_match('/(https|http):\/\/vk\.me\/join/', $user_text))
            {
                $response->message = 'Ссылка в тексте на вк, не гуд!';
                return $response;
            }
            if (is_numeric($params[0]) && $params[1] != '' && $peer->title != false && $ts != $params[0]) {
                $str = str_replace("{$params[0]}", '', $user_text);
                if ($this->user->is_dev == 1) {
                    $nick = "[id{$this->user->id}|Разработчика бота]";
                } else
                    $nick = "[id{$this->user->id}|{$this->user->first_name_nom}]";
                $peer = Peer::findById(\App::$peerStartNumber + $params[0]);
                if($this->peer->getSetting(21) == 1)
                {
                    if($peer->getSetting(21) == 1)
                    {
                        $this->vk->messagesSend($peer->id, $str . PHP_EOL . "Сообщение от {$nick}" . PHP_EOL . "Беседа {$this->peer->title} (айди беседы {$ts})" . PHP_EOL . "Чтобы ответить введите команду написать.");
                        $response->message = "Ваш текст был отправлен в беседу {$peer->title}";
                    }
                    else
                        $response->message = "В указанную беседу запрещено отправлять сообщения";
                }
                else
                    $response->message = "В вашей беседе запрещен обмен сообщениями с другими беседами";
            } elseif ($params[1] == '' && is_numeric($params[0])) {
                $response->message = "Введите текст после {$user_text}.";
            } elseif ($peer->title == false) {
                $response->message = "Беседы с таким айди не существует, попробуйте команду беседы.";
            } elseif (!is_numeric($params[0])) {
                $response->message = "Введите айди беседы после слова написать.";
            } elseif ($ts == $params[0]) {
                $response->message = "Вы пытаетесь написать в свою же беседу.";
            }
        } else {
            if ($this->peer->getSetting(16) == 1)
                $response->message = "У вас слишком маленький статус. Необходимый статус {$this->peer->getSetting(14)}";
        }
        return $response;
    }

    public function PeersAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $number = 1;
            $answer = '';
            $peer = Peer::RandPeer();
            while ($number != 6) {
                $id = rand(1, count($peer));
                $ts = $peer[$id]['id'] - 2000000000;
                $answer .= $number . ") Беседа {$peer[$id]['title']} имеет id = {$ts}" . PHP_EOL;
                $number++;
            }
            $response->message = "Беседы:" . PHP_EOL . $answer;
        }
        return $response;
    }

    public function ShipShipAction($user_text)
    {
        $response = new Response();
        $response->peer_id = $this->peer->id;
        if ($user_text == '') {
            $users = [];
            $data = $this->userPeer->getRandomUserBySex();
            foreach ($data as $datum) {
                $id1 = array_rand($datum[0]);
                array_push($users, $datum[0][$id1]);
            }
            $user1 = new User($users[0]['user_id']);
            $user2 = new User($users[1]['user_id']);
            $response->message = "Вас зашиперили, хехе:" . PHP_EOL . $user1->getName() . " и " . $user2->getName();
        } else {
            $params = explode(' ', $user_text);
            $id1 = $params[0];
            $id2 = $params[1];
            $regUser = "~\[id(?<id>[0-9]*)\|[^\[\]\|]*\]~";
            $regUrl = "~https://vk.com/(?<domain>.*)~";
            $regId = "~https://vk.com/id(?<id>[0-9]*)~";
            if (preg_match($regUser, $id1, $matches)) {
                $user_id = intval($matches['id']);
                $user1 = new User($user_id);
            } elseif (preg_match($regId, $id1, $matches)) {
                $user1 = User::findById($matches['id']);
            } elseif (preg_match($regUrl, $id1, $matches)) {
                $user1 = User::findByDomain($matches['domain']);
            } else {
                $user1 = new User(0);
            }
            $regUser = "~\[id(?<id>[0-9]*)\|[^\[\]\|]*\]~";
            $regUrl = "~https://vk.com/(?<domain>.*)~";
            $regId = "~https://vk.com/id(?<id>[0-9]*)~";
            if (preg_match($regUser, $id2, $matches)) {
                $user_id = intval($matches['id']);
                $user2 = new User($user_id);
            } elseif (preg_match($regId, $id2, $matches)) {
                $user2 = User::findById($matches['id']);
            } elseif (preg_match($regUrl, $id2, $matches)) {
                $user2 = User::findByDomain($matches['domain']);
            } else {
                $user2 = new User(0);
            }
            if ($user1->id > 0 && $user2->id > 0) {
                if ($user1->id != $user2->id) {
                    $response->message = "Вас зашиперили, хехе:" . PHP_EOL . $user1->getName() . " и " . $user2->getName();
                }
            }
        }
        return $response;
    }
}