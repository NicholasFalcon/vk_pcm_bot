<?php

use controller\control\AdminController;
use core\Routing;

Routing::setCommandForPeer('edit_user_role :role_id :user_id', AdminController::class, 'changeUserRole');

Routing::group('роль', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'editRole');
});

Routing::group('беседа', function () {
    Routing::group('инфо', function () {
        Routing::setForPeer('', AdminController::class, 'ChatInfo');
    });
});
Routing::group('чат', function () {
    Routing::group('инфо', function () {
        Routing::setForPeer('', AdminController::class, 'ChatInfo');
    });
});

Routing::group('модуль', function () {
    Routing::setForPeer('администрирование', AdminController::class, 'Administration');
    Routing::setForPeer('участник', AdminController::class, 'Profile');
    Routing::setForPeer('беседа', AdminController::class, 'Peer');
    Routing::setForPeer('сетка', AdminController::class, 'Web');
    Routing::setForPeer('команды', AdminController::class, 'Commands');
    Routing::setForPeer('действия', AdminController::class, 'RpCommands');
    Routing::setForPeer('кланы', AdminController::class, 'Clans');
    Routing::setForPeer('игры', AdminController::class, 'Games');
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
    Routing::setForPeer('-мут', AdminController::class, 'MutePeerRemove');
});
Routing::group('пред', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'warning');
});
Routing::group('-пред', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'removeWarning');
});
Routing::group('тотал', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'total');
});
Routing::group('-тотал', function () {
    Routing::setForPeer(':user_text', AdminController::class, 'removeTotal');
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
Routing::group('бан', function () {
    Routing::group('лист', function () {
        Routing::setForPeer('', AdminController::class, 'BanList');
    });
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

Routing::group('чатссылка', function () {
    Routing::setForPeer('', AdminController::class, 'ChatUrl');
    Routing::group('установить', function () {
        Routing::setForPeer(':user_text', AdminController::class, 'SetChatUrl');
    });
    Routing::setForPeer('удалить', AdminController::class, 'DeleteChatUrl');
});
Routing::group('правила', function () {
    Routing::setForPeer('', AdminController::class, 'Rules');
    Routing::group('установить', function () {
        Routing::setForPeer(':user_text', AdminController::class, 'RulesSet');
    });
    Routing::setForPeer('удалить', AdminController::class, 'RulesDeleted');
});
Routing::group('приветствие', function () {
    Routing::setForPeer('', AdminController::class, 'HelloMessage');
    Routing::group('установить', function () {
        Routing::setForPeer(':user_text', AdminController::class, 'SetHelloMessage');
    });
    Routing::setForPeer('удалить', AdminController::class, 'HelloMessageDeleted');
});

Routing::group('кто', function () {
    Routing::group('добавил', function () {
        Routing::setForPeer('', AdminController::class, 'invitedBy');
    });
    Routing::group('пригласил', function () {
        Routing::setForPeer('', AdminController::class, 'invitedBy');
    });
});

Routing::group('админы', function () {
    Routing::setForPeer('беседы', AdminController::class, 'getAdmins');
});