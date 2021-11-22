<?php
use model\User;
use comboModel\UserPeer;
use model\Hero;
use model\Clan;

/**
 * @var $hero Hero
 * @var $user User
 * @var $userPeer UserPeer
 */

if ($hero->national == 1)
    $national = "Живой Природы";
elseif ($hero->national == 2)
    $national = "Кровавого Пера";
elseif ($hero->national == 3)
    $national = "Ночных Теней";
elseif ($hero->national == 4)
    $national = "Цветущих Роз";
?>
<?= $hero->class ." ". $national . PHP_EOL?>
<?php echo ("🏅Уровень: {$hero->level}" . PHP_EOL)?>
<?php echo ("⚔Атака: {$hero->atk}  🛡Защита: {$hero->def}" . PHP_EOL)?>
<?php echo ("💥Опыт: {$hero->exp} /". (15 * 2.3 * $hero->level * 4) . PHP_EOL)?>
<?php echo ("💡Выносливость: {$hero->stamina}/{$hero->max_stamina} ");
if ($hero->stamina != $hero->max_stamina) echo (round(($hero->stamina_tst - time())/60, 0 , PHP_ROUND_HALF_UP) . " мин." . PHP_EOL); else echo ('') . PHP_EOL?>
<?php echo ("💰Монеток: {$hero->gold}" . PHP_EOL)?>

<?php echo ("Состояние: {$hero->status}");?>