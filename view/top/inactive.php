<?php

use model\User;

/**
 * @var $userInfo []
 * @var $title string
 * @var $timeInactive int
 */

?>
<?php $number = 0;?>
<?=$title.PHP_EOL?>
<?php foreach ($userInfo as $user):
$time = intval((time() - $userInfo[$number]['last_tst'])/3600);
if ($time == 1) {$text = 'час';} elseif ( 4 >= $time && $time >= 2) {$text = 'часа';} else {$text = 'часов';}?>
<?= "💤 ". (new User($user['user_id']))->getName() . " уже молчит: " . (($user['last_tst'] <= $timeInactive)?"неактив, будет кикнут при чистке":(intval((time() - $user['last_tst'])/3600)) . " {$text}"). PHP_EOL?>
<?php $number++; endforeach;?>
