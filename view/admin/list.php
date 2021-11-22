<?php
/**
 * @var $admins []
 * @var $peer \model\Peer
 */
?>
Роль: главные админы<?=PHP_EOL?>
<?php foreach ($admins as $admin):?>
[id<?=$admin['id']?>|<?=$admin['first_name_nom']?> <?=$admin['last_name_nom']?>]<?=PHP_EOL?>
<?php endforeach;?>