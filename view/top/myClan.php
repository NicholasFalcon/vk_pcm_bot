<?php
/**
 * @var $clan []
 */

use model\User;
use model\Clan;

?>
<?= "Клан: {$clan->title} {$clan->clan_pin}"
. PHP_EOL . "Участников: " . (new Clan($clan->id))->findCountMember() .  "/{$clan->max_member}"
. PHP_EOL . "Создатель: " . (new User($clan->owner_id))->GetName()
. PHP_EOL . "Уровень: {$clan->level}, Славы: {$clan->glory}/{$clan->need_glory}"?>
