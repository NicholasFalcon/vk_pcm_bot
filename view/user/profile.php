<?php
use model\User;
use model\Peer;
use comboModel\UserPeer;
use model\Warning;
use model\Wedding;
use model\Clan;
use model\Web;

/**
 * @var $user User
 * @var $userPeer UserPeer
 * @var $level
 * @var $peer Peer
 */

$nameStatus = '';
$wed = Wedding::findByUserId($user->id, $peer->id);
$day = intval((time() - $userPeer->reg_tst) / (3600*24));
$clan = Clan::findClanByMember($user->id);
$web = Web::findWebByOwner($user->id);
if ($userPeer->getRegDay() == 1) {$text = 'день';} elseif ( 4 >= $userPeer->getRegDay() && $userPeer->getRegDay() >= 2) {$text = 'дня';} else {$text = 'дней';}
if ($wed !== false){ $dayWed = intval((time() - $wed->data_tst) / (3600*24)); $wed_user = new User($wed->getPartner());}
?>
Профиль <?=$user->getFullName('gen')?>:
В беседе с: <?=date('Y-m-d H:i:s', $userPeer->reg_tst)?> (<?= $userPeer->getRegDay() . " {$text}"?>)
<?php if ($user->nick != null) {echo ("Ник: " . $user->nick . PHP_EOL);}
else echo ("У вас ещё нет ника.". PHP_EOL) ?>
<?php if ($user->pin != null) {echo ("Значок: " . $user->pin . PHP_EOL);}?>
<?php if ($clan !== false) {echo ("Клан: " . $clan->title . $clan->clan_pin . PHP_EOL);}?>
<?php if (Warning::getWarnings($userPeer) != 0) echo ("Предупреждений: " . Warning::getWarnings($userPeer) . PHP_EOL)?>
<?php if($wed !== false) { echo ("В браке с: " . $wed_user->getName() ." уже " . $dayWed . " день". PHP_EOL ); }?>
Статистика за день.
Символов: <?=$userPeer->char_day?> | Сообщений: <?=$userPeer->msg_day . PHP_EOL?>
Последнее сообщение: <?=date('Y-m-d H:i:s', $userPeer->last_tst) . PHP_EOL ?>
Ваш уровень в беседе = <?=  $level . PHP_EOL ?>
Для апа ЛВЛа надо <?= $userPeer->char_all . "/" . pow(2,$level+1)*1000 . " символов." . PHP_EOL?>
<?php if ($web != false && $userPeer->user_id == $web->owner_id) echo "Выдал глобанов {$web->count_globans} в сетке {$web->name}" . PHP_EOL?>
<?php if ($user->is_dev == 1) echo("Разработчик Бота"); else echo ("Роль: ".$userPeer->getRoleName())?>
