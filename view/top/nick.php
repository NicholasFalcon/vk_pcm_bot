<?php
/**
 * @var $userInfo []
 * @var $title string
 */
?>
<?php $number = 1 ?>
<?= $title . PHP_EOL ?>
<?php foreach ($userInfo as $user):?>
<?= $number . ") {$user['first_name_nom']} {$user['last_name_nom']} - {$user['nick']}" . PHP_EOL?>
<?php $number++?>
<?php endforeach;?>