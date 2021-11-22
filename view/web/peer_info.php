<?php
/**
 * @var $peer \model\Peer
 * @var $owner \model\User
 * @var $adminList string
 */
?>
    ───────────────
    ✏Название беседы: <?=$peer->title?><?=PHP_EOL?>
    👑Создатель беседы: <?=$owner->getName()?><?=PHP_EOL?>
    ⚙Было участников: <?=$peer->users_count_old?> Стало: <?=$peer->users_count?><?=PHP_EOL?>
    🔪Киков за вчера и сегодня: <?=$peer->count_kick_old?>/<?=$peer->count_kick?><?=PHP_EOL?>
    🎗Администраторы беседы:<?=PHP_EOL?>

<?=$adminList?>