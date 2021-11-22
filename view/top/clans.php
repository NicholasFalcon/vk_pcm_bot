<?php

/**
 * @var $clans []
 * @var $title string
 */

use model\Clan;
use model\User;

?>
<?php $number = 1?>
<?=$title.PHP_EOL?>
<?php foreach ($clans as $clan):?>
<?=$number .") " ." {$clan['title']}{$clan['clan_pin']} "
. PHP_EOL ."Участников: " . ((new Clan($clan['id']))->findCountMember()) . "/{$clan['max_member']}"
. PHP_EOL . "Создатель: " . (new User($clan['owner_id']))->GetName()
. PHP_EOL . "Уровень: {$clan['level']}, Славы: {$clan['glory']}" . PHP_EOL?>
<?php $number++?>
<?php endforeach;?>
