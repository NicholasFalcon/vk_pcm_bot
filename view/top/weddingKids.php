<?php

use model\User;

/**
 * @var $weddings []
 * @var $title string
 */
?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($weddings as $wedding):?>
<?=$number . ") " . (new User($wedding['user_id']))->getName() . " {$wedding['age']} лет." . PHP_EOL . "Был зачат в " . date('Y-m-d H:i:s', $wedding['sex_tst']) . PHP_EOL?>
<?php $number++?>
<?php endforeach;?>
