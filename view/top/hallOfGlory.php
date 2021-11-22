<?php

use model\User;
use comboModel\UserPeer;
use model\Warning;
use model\Wedding;
use model\Clan;
use model\Web;

/**
 * @var $title string
 * @var $user_top_level []
 * @var $top_clan_glory []
 * @var $level []
 * @var $top_peer []
 * @var $day_top_peer
 */
?>
<?= $title . PHP_EOL?>
1) Максимальный уровень <?= "{$level[0]['level']}"?> у <?= "[id{$level[0]['user_id']}|{$level[0]['first_name_nom']} {$level[0]['last_name_nom']}]" . PHP_EOL?>
2) Больше всего символов <?= "{$user_top_level[0]['length']}"?> у <?= "[id{$user_top_level[0]['user_id']}|{$user_top_level[0]['first_name_nom']} {$user_top_level[0]['last_name_nom']}]" . PHP_EOL?>
3) Больше всего сообщений <?= "{$user_top_level[0]['msg']}"?> у <?= "[id{$user_top_level[0]['user_id']}|{$user_top_level[0]['first_name_nom']} {$user_top_level[0]['last_name_nom']}]" . PHP_EOL?>
4) Больше всего славы <?= "{$top_clan_glory[0]['glory']}" ?> у клана <?= "{$top_clan_glory[0]['title']}" ?>  <?= "[id{$top_clan_glory[0]['owner_id']}|Лидер клана] " . PHP_EOL?>
5) Активная беседа за всё время <?= "{$top_peer[0]['title']} " . "{$top_peer[0]['length']}/{$top_peer[0]['msg']}" . PHP_EOL?>
6) Активная беседа за день <?= "{$day_top_peer[0]['title']} " . "{$day_top_peer[0]['length']}/{$day_top_peer[0]['msg']}" . PHP_EOL?>