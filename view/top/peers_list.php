<?php
/**
 * @var $peers []
 * @var $title string
 */
?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($peers as $peer):?>
<?=(($number <= 3)?"👑 ":"").$number . ")"." {$peer['title']}: {$peer['length']} | {$peer['msg']}".PHP_EOL?>
<?php $number++?>
<?php endforeach;?>
