<?php

use controller\control\AdminController;
use core\Routing;
use Validation\Validation;
use Validation\Validators\IntValidator;
use Validation\Validators\RequireValidator;
use Validation\Validators\WordValidator;

Routing::setCommandForPeer('edit_user_role :role_id :user_id', AdminController::class, 'changeUserRole');

Routing::group('роль', function () {
    Routing::setForPeer(':username', AdminController::class, 'editRole', Validation::create()
        ->setValidation('username', Validation::FULL));
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
    Routing::setForPeer(':username', AdminController::class, 'kick', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('бан', function () {
    Routing::setForPeer(':username', AdminController::class, 'ban', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('мут', function () {
    Routing::setForPeer(':time :username', AdminController::class, 'muteUser', Validation::create()
        ->setValidation('time', IntValidator::create(), RequireValidator::create())
        ->setValidation('username', Validation::FULL));
});
Routing::group('-бан', function () {
    Routing::setForPeer(':username', AdminController::class, 'removeBan', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('-мут', function () {
    Routing::setForPeer(':username', AdminController::class, 'removeMuteUser', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('беседа', function () {
    Routing::group('мут', function () {
        Routing::setForPeer(':time', AdminController::class, 'MutePeer', Validation::create()
            ->setValidation('time', IntValidator::create(), RequireValidator::create()));
    });
    Routing::setForPeer('-мут', AdminController::class, 'MutePeerRemove');
});
Routing::group('пред', function () {
    Routing::setForPeer(':username', AdminController::class, 'warning', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('-пред', function () {
    Routing::setForPeer(':username', AdminController::class, 'removeWarning', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('тотал', function () {
    Routing::setForPeer(':username', AdminController::class, 'total', Validation::create()
        ->setValidation('username', Validation::FULL));
});
Routing::group('-тотал', function () {
    Routing::setForPeer(':username', AdminController::class, 'removeTotal', Validation::create()
        ->setValidation('username', Validation::FULL));
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
Routing::group('бан', function () {
    Routing::group('лист', function () {
        Routing::setForPeer('', AdminController::class, 'BanList');
    });
});
Routing::setForPeer('молчуны', AdminController::class, 'sleepers');
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
        Routing::setForPeer(':url', AdminController::class, 'SetChatUrl', Validation::create()
            ->setValidation('url', WordValidator::create()));
    });
    Routing::setForPeer('удалить', AdminController::class, 'DeleteChatUrl');
});
Routing::group('правила', function () {
    Routing::setForPeer('', AdminController::class, 'Rules');
    Routing::group('установить', function () {
        Routing::setForPeer(':rules', AdminController::class, 'RulesSet', Validation::create()
            ->setValidation('rules', Validation::FULL, RequireValidator::create()));
    });
    Routing::setForPeer('удалить', AdminController::class, 'RulesDeleted');
});
Routing::group('приветствие', function () {
    Routing::setForPeer('', AdminController::class, 'HelloMessage');
    Routing::group('установить', function () {
        Routing::setForPeer(':hello_message', AdminController::class, 'SetHelloMessage', Validation::create()
            ->setValidation('hello_message', Validation::FULL, RequireValidator::create()));
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