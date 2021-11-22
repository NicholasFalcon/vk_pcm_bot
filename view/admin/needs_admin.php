<?php
/**
 * @var $admins []
 * @var $allCount
 * @var $count
 */
use model\User;
?>
<?php $number = 1?>
<?= "Всего в беседах {$allCount} админ, не хватает {$count} админов в вашей беседе." . PHP_EOL?>
<?php foreach ($admins as $admin):?>
<?php $user = new User($admin)?>
<?php  echo ("{$number}) ". $user->getName('gen') . " Нет в беседе, хотя он является админом." . PHP_EOL)?>
<?php $number++?>
<?php if ($number > 10) break?>
<?php endforeach;?>
<?php
