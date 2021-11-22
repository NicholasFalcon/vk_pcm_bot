<?php
/**
 * @var $users []
 * @var $title string
 */
?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($users as $user):?>
<?=(($number <= 3)?"👑 ":"").$number . ") {$user['first_name_nom']} {$user['last_name_nom']}: {$user['length']} | {$user['msg']} (Сила спама - ".intdiv($user['length'], $user['msg']).")".PHP_EOL?>
<?php $number++?>
<?php endforeach;?>
