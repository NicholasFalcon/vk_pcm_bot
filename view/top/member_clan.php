<?php

use model\User;

/**
 * @var $members []
 * @var $title string
 */

?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($members as $member):?>
<?=$number . ") " . (new User($member['member_id']))->getName() .PHP_EOL?>
<?php $number++?>
<?php endforeach;?>
