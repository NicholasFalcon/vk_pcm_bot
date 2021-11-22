<?php

use model\User;

/**
 * @var $Rewards []
 * @var $user User
 */
?>
<?php $number = 1 ?>
<?= $user->getName() . " имеет следующие награды:" . PHP_EOL?>
<?php foreach ($Rewards as $Reward):?>
<?= $number . ") "  . PHP_EOL?>
<?php $number++?>
<?php endforeach;?>