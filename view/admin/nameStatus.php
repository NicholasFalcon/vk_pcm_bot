<?php

use model\Peer;
/**
 * @var $title
 * @var $peer Peer
 */
?>
<?php echo ($title . PHP_EOL)?>
<?php echo ("Статус 5: {$peer->status5_name}" . PHP_EOL)?>
<?php echo ("Статус 4: {$peer->status4_name}" . PHP_EOL)?>
<?php echo ("Статус 3: {$peer->status3_name}" . PHP_EOL)?>
<?php echo ("Статус 2: {$peer->status2_name}" . PHP_EOL)?>