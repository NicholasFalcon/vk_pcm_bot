<?php
/**
 * @var $webs []
 */
?>
Список ваших сеток:
<?php foreach ($webs as $web):?>
Айди = <?=  $web['id']?> Название:  <?=$web['name'].PHP_EOL?>
<?php endforeach;?>
