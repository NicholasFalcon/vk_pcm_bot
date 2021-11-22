<?php
/**
 * @var $web \model\Web
 * @var $owner string
 * @var $webCreator \model\User
 */
?>
Название сетки: <?=$web->name?><?=PHP_EOL?>
Айди сетки: <?=$web->id?><?=PHP_EOL?>
Создатель сетки: <?=$webCreator->getName()?><?=PHP_EOL?>
Создатель беседы: <?=$owner?><?=PHP_EOL?>
Администраторы сетки:
<?php foreach ($web->getAdmins() as $admin):?>
<?=$admin->getName() . PHP_EOL ?>
<?php endforeach;?>
