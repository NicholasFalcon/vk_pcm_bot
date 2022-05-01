<?php

use controller\control\AdminController;
use core\Routing;

Routing::group('администрирование', function () {
    Routing::setForPeer('', AdminController::class, 'Administration');
});
Routing::group('участник', function () {
    Routing::setForPeer('', AdminController::class, 'Profile');
});
Routing::group('беседа', function () {
    Routing::setForPeer('', AdminController::class, 'Peer');
});
Routing::group('сетка', function () {
    Routing::setForPeer('', AdminController::class, 'Web');
});
Routing::group('команды', function () {
    Routing::setForPeer('', AdminController::class, 'Commands');
});
Routing::group('действия', function () {
    Routing::setForPeer('', AdminController::class, 'RpCommands');
});
Routing::group('кланы', function () {
    Routing::setForPeer('', AdminController::class, 'Clans');
});
Routing::group('игры', function () {
    Routing::setForPeer('', AdminController::class, 'Games');
});
Routing::group('назад', function () {
    Routing::setForPeer('', AdminController::class, 'Module');
});
Routing::group('настройки', function () {
    Routing::setForPeer('', AdminController::class, 'Module');
});

Routing::group('кик', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'kick');
});
Routing::group('бан', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'ban');
});
Routing::group('мут', function () {
        Routing::setForPeer(':user_text', AdminController::class, 'muteUser');
});
Routing::group('-бан', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'removeBan');
});
Routing::group('-мут', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'removeMuteUser');
});
Routing::group('беседа', function () {
    Routing::group('мут', function () {
        Routing::setForPeer(':user_text', AdminController::class, 'MutePeer');
    });
});
Routing::group('беседа', function () {
    Routing::setForPeer('-мут', AdminController::class, 'MutePeerRemove');
});
Routing::group('пред', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'warning');
});
Routing::group('-пред', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'removeWarning');
});

Routing::group('кого', function () {
    Routing::setForPeer('нет', AdminController::class, 'findAdminsByWeb');
});
Routing::group('созвать', function () {
    Routing::group('онлайн', function () {
        Routing::setForPeer('', AdminController::class, 'getOnline');
    });
});
Routing::group('преды', function () {
    Routing::setForPeer('', AdminController::class, 'allWarning');
});
Routing::group('молчуны', function () {
    Routing::setForPeer('', AdminController::class, 'sleepers');
});
Routing::group('чатссылка', function () {
    Routing::setForPeer('', AdminController::class, 'ChatUrl');
    Routing::group('установить', function () {
        Routing::setForPeer(':user_text', AdminController::class, 'SetChatUrl');
    });
    Routing::setForPeer('удалить', AdminController::class, 'DeleteChatUrl');
});
Routing::group('кик', function () {
    Routing::group('собачек', function () {
        Routing::setForPeer('', AdminController::class, 'KickDeactivated');
    });
});
Routing::group('кик', function () {
    Routing::group('вышедших', function () {
        Routing::setForPeer('', AdminController::class, 'KickLeavers');
    });
});