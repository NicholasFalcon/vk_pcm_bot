<?php


/**
 * @var $triggers []
 * @var $title string
 */
?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($triggers as $trigger):?>
<?="{$trigger['command']}" .PHP_EOL?>
<?php endforeach;?>
