<?php
/**
 * @var $web \model\Web
 */
?>
    Название сетки: <?=$web->name?><?=PHP_EOL?>
    Айди сетки: <?=$web->id?><?=PHP_EOL?>
    Администраторы сетки:
<?php foreach ($web->getAdmins() as $admin):?>
    <?=$admin->getName() . PHP_EOL ?>
<?php endforeach;?>