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
    <?=$number . ") " . (new User($wedding['mother']))->getName() . " и " . (new User($wedding['father']))->getName() . PHP_EOL?>
    <?php $number++?>
<?php endforeach;?>
