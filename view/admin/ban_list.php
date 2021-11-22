<?php
/**
 * @var $peer_id
 * @var $title
 *  @var $users []
 */

use model\User;

?>
<?php echo ($title) . PHP_EOL?>
<?php foreach ($users as $NeedUser):?>
<?php $user = new User($NeedUser['user_id'])?>
<?php echo $user->getName() . ", "?>
<?php endforeach;?>