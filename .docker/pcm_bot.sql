create table birsha
(
    id_user      int not null
        primary key,
    id_birsha    int null,
    count        int null,
    id_resources int null,
    birsha_tst   int null
);

create table bugs
(
    id   int auto_increment
        primary key,
    info text null
);

create table clan
(
    id         int auto_increment
        primary key,
    title      char(255)       null,
    clan_pin   char(255)       null,
    owner_id   int             null,
    max_member int default 10  null,
    level      int default 1   null,
    glory      int default 0   not null,
    need_glory int default 150 null,
    max_level  int default 8   null
);

create table clan_member
(
    clan_id   int           not null,
    member_id int           not null,
    is_active int default 1 null,
    primary key (clan_id, member_id)
);

create table global_params
(
    id     int          not null
        primary key,
    name   varchar(255) null,
    param1 int          null
);

create table `groups`
(
    id       int auto_increment
        primary key,
    have_ban int default 0 null,
    message  text          null,
    name     varchar(255)  null,
    domain   varchar(255)  null
);

create table hero
(
    id          int                       not null
        primary key,
    class       char(255) default 'Воин'  null,
    atk         int       default 1       null,
    def         int       default 1       null,
    exp         int       default 0       null,
    stamina_tst int                       null,
    max_stamina int       default 5       null,
    stamina     int       default 5       null,
    status      char(255) default 'Отдых' null,
    gold        int       default 0       null,
    level       int       default 1       null,
    gang        int       default 0       null,
    national    int                       null,
    target      int                       null
);

create table peers
(
    id              int           not null
        primary key,
    title           text          null,
    owner_id        int default 0 null,
    init            int default 0 null,
    web_id          int default 0 null,
    url             char(255)     null,
    autokick        int default 0 null,
    users_count     int default 1 null,
    users_count_old int default 0 null,
    count_kick      int default 0 null,
    count_kick_old  int default 0 null,
    HelloMessage    varchar(4500) null,
    days            int default 0 null,
    MutePeer        int default 0 null,
    rules           varchar(4500) null
);

create table callback
(
    action  varchar(255)  null,
    params  text          null,
    tst     int default 0 null,
    user_id int           null,
    peer_id int           null,
    constraint callback_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table game_by_peer
(
    id         int auto_increment
        primary key,
    peer_id    int           not null,
    title      char(255)     null,
    checker    int default 0 null,
    need_word  char(255)     not null,
    wrong      char(255)     null,
    `right`    char(255)     null,
    numb_shoot int default 0 null,
    timer      int           null,
    constraint game_by_peer_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table groups_peer_info
(
    group_id int           null,
    peer_id  int           null,
    have_ban int default 0 null,
    is_admin int default 0 null,
    deleted  int default 0 null,
    constraint groups_peer_info_group_id_peer_id_uindex
        unique (group_id, peer_id),
    constraint groups_peer_info_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table notification
(
    message text          null,
    peer_id int           null,
    tst     int default 0 null,
    constraint notification_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table peers_settings
(
    peer_id    int not null,
    setting_id int not null,
    value      int null,
    primary key (peer_id, setting_id),
    constraint peers_settings_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table resources
(
    id_resources int          not null
        primary key,
    name         varchar(255) null,
    tier_level   int          null
);

create table resources_by_users
(
    id_users     int null,
    id_resources int not null
        primary key,
    count        int null
);

create table rewards
(
    title        varchar(255)  null,
    user_id      int           null,
    peer_id      int           null,
    user_send_id int           null,
    reward_tst   int           null,
    type_reward  int default 5 null,
    constraint rewards_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table roles
(
    id       int auto_increment
        primary key,
    owner_id int default 0 null,
    title    varchar(50)   null
);

create table roles_access
(
    role_id   int null,
    access_id int null
);

create index roles_access_role_id_access_id_index
    on roles_access (role_id, access_id);

create table skills
(
    column_1   int          null,
    id         int          not null
        primary key,
    name       varchar(255) null,
    level      int          null,
    parameters int          null
);

create table skills_by_users
(
    id_user  int          not null
        primary key,
    id_skill int          null,
    level    int          null,
    name     varchar(255) null
);

create table spam
(
    id       int           null,
    count    int default 0 null,
    last_tst int default 0 null
);

create table sysinfo
(
    id     int auto_increment
        primary key,
    day_id int default 0 null
);

create table triggers
(
    id           int auto_increment
        primary key,
    text_trigger text          null,
    peer_id      int default 0 not null,
    command      text          null,
    attach       varchar(100)  null,
    constraint triggers_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create index triggers_attach_index
    on triggers (attach);

create table user_confirmation
(
    id        int auto_increment
        primary key,
    user_id   int           not null,
    tst       int default 0 null,
    type_id   int           not null,
    json_data text          null,
    peer_id   int           not null
);

create table user_web_info
(
    user_id  int           null,
    web_id   int           null,
    have_ban int default 0 null,
    is_admin int default 0 null,
    constraint user_web_info_user_id_web_id_uindex
        unique (user_id, web_id)
);

create table users
(
    id             int                           not null
        primary key,
    first_name_nom varchar(255)                  null,
    last_name_nom  varchar(255)                  null,
    sex            int          default 0        null,
    domain         varchar(255)                  null,
    first_name_gen varchar(255)                  null,
    last_name_gen  varchar(255)                  null,
    first_name_dat varchar(255)                  null,
    last_name_dat  varchar(255)                  null,
    first_name_acc varchar(255)                  null,
    last_name_acc  varchar(255)                  null,
    first_name_ins varchar(255)                  null,
    last_name_ins  varchar(255)                  null,
    first_name_abl varchar(255)                  null,
    last_name_abl  varchar(255)                  null,
    nick           varchar(50)                   null,
    pin            varchar(4)                    null,
    is_callable    int          default 1        null,
    is_dev         int          default 0        null,
    api_key        varchar(20)                   null,
    checker        int          default 0        null,
    first          varchar(255)                  null,
    second         varchar(255)                  null,
    country        varchar(255) default 'Россия' null,
    stiker         int          default 0        null,
    black_list     int          default 0        null,
    user_action    varchar(50)                   null
);

create table users_peer_info
(
    peer_id      int           not null,
    user_id      int           not null,
    have_ban     int default 0 null,
    role_id      int default 1 null,
    is_admin     int default 0 null,
    msg_day      int default 0 null,
    char_day     int default 0 null,
    msg_week     int default 0 null,
    char_week    int default 0 null,
    msg_all      int default 0 null,
    char_all     int default 0 null,
    last_tst     int default 0 null,
    reg_tst      int           null,
    deleted      int default 0 null,
    muted        int default 0 null,
    `check`      int default 0 null,
    level        int default 1 null,
    kick_by_peer int default 0 null,
    ban_by_peer  int default 0 null,
    primary key (peer_id, user_id),
    constraint users_peer_info_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table warnings
(
    id      int auto_increment
        primary key,
    user_id int           not null,
    peer_id int           not null,
    tst     int default 0 not null,
    constraint warnings_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table webs
(
    id            int auto_increment
        primary key,
    name          varchar(255)  null,
    owner_id      int           null,
    count_globans int default 0 null
);

create table webs_settings
(
    web_id     int default 0 not null,
    setting_id int default 0 not null,
    value      int           null,
    primary key (web_id, setting_id)
);

create table wedding_kids
(
    id      int auto_increment
        primary key,
    user_id int           null,
    mother  int           null,
    father  int           null,
    peer_id int           null,
    sex_tst int default 0 null,
    age     int default 0 null,
    constraint wedding_kids_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table weddings
(
    id         int auto_increment
        primary key,
    peer_id    int           null,
    first_user int           not null,
    sec_user   int           not null,
    data_tst   int           null,
    points     int default 0 null,
    constraint weddings_peers_id_fk
        foreign key (peer_id) references peers (id)
            on update cascade on delete cascade
);

create table words
(
    id    int auto_increment
        primary key,
    word  char(255) null,
    topic char(255) null
);

create table settings
(
    id            int auto_increment,
    name          varchar(100) null,
    title         varchar(100) null,
    default_value text         null,
    type          varchar(100) null,
    constraint settings_pk
        primary key (id)
);

create unique index settings_name_uindex
    on settings (name);

INSERT INTO roles (owner_id, title) VALUES (DEFAULT, 'Участник');
INSERT INTO roles (owner_id, title) VALUES (DEFAULT, 'Главный админ');

INSERT INTO settings (`name`, title, default_value, `type`) VALUES ('kick_leavers', 'Кикать вышедших пользователей?', '0', 'boolean'),
                                                                   ('use_triggers', 'Отключить триггеры?', '0', 'boolean'),
                                                                   ('number_warn', 'Количество предов', '3', 'int'),
                                                                   ('time_kick', 'Кик неактива через (дней):', '7', 'int'),
                                                                   ('kick_inactive', 'Кикать неактив?', '1', 'boolean'),
                                                                   ('kick_url', 'Кикать за ссылки?', '0', 'boolean'),
                                                                   ('kick_invite_url', 'Кикать за ссылки на беседы?', '0', 'boolean'),
                                                                   ('kick_spam', 'Кикать за спам (4 сообщения)?', '0', 'boolean'),
                                                                   ('kick_group', 'Кикать сообщества?', '1', 'boolean'),
                                                                   ('Attention', 'Сколько раз в день можно созвать всех?', '2', 'int'),
                                                                   ('KickGroup', 'Можно ли кикать сообщества?', '0', 'boolean'),
                                                                   ('ColorButton', 'Цвет всех кнопок? (1 зелёный, 2 красный, 3 белый, 4 синий)', '1', 'int'),
                                                                   ('HelloMessage', 'Включить приветствие?', '1', 'boolean'),
                                                                   ('Rules', 'Писать правила вместе с приветствием?', '0', 'boolean'),
                                                                   ('MessagesPeer', 'Принимать сообщения от других бесед?', '1', 'boolean');