<?php

use model\User;
use model\Warning;

/**
 * @var $warnings []
 * @var $title string
 */
?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($warnings as $warning):?>
<?= $number . ") "."{$warning['first_name_nom']} {$warning['last_name_nom']} имеет {$warning['count']} предупреждение".PHP_EOL?>
<?php $number++?>
<?php endforeach;?>
