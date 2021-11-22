<?php

use model\User;

/**
 * @var $weddings []
 * @var $title string
 */
?>
<?php $number = 1 ?>
<?= $title . PHP_EOL ?>
<?php foreach ($weddings as $wedding): ?>
<?php $dayWed = intval((time() - $wedding['data_tst']) / (3600 * 24)) ?>
<?= $number . ") " . (new User($wedding['first_user']))->getName() . " и " . (new User($wedding['sec_user']))->getName() . " уже " . $dayWed . " дней.". PHP_EOL ?>
<?php $number++ ?>
<?php endforeach; ?>
