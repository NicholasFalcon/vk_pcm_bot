<?php
/**
 * @var $admins []
 */
use model\User;

?>
<?php $currentStatus = 0?>
<?php foreach ($admins as $admin):?>
<?php if($currentStatus != $admin['status']):?>
<?php endif;?>
<?php if ($admin['user_id'] > 0) echo "[id{$admin['user_id']}|{$admin['first_name_nom']}]" . PHP_EOL;  ?>
<?php endforeach;?>
