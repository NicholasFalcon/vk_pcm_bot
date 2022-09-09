<?php
/**
 * copyright by Nikolay Sokolov (Hironori) (c)
 */

namespace api;


class Vk
{
    const scopes = [
        'notify',
        'friends',
        'photos',
        'audio',
        'video',
        'stories',
        'pages',
        'status',
        'notes',
        'messages',
        'wall',
        'ads',
        'offline',
        'docs',
        'groups',
        'notifications',
        'stats',
        'email',
        'market'
    ];

    const scopesGroup = [
        'messages',
        'manage',
        'photos',
        'docs',
        'wall',
        'stories'
    ];

    private string $token;
    private string $version;
    private string $domain;
    public int $id;

    function __construct($token, $version, $domain, $id)
    {
        $this->domain = $domain;
        $this->id = $id;
        $this->token = $token;
        $this->version = $version;
    }

    private function curlExec($url, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        curl_close ($ch);
        return json_decode($server_output, true);
    }

    public function checkUpdateLongPollServer($server, $key, $ts)
    {
        $urlLP = "$server?act=a_check&key=$key&ts=$ts&wait=25";

        return $this->curlExec($urlLP, []);
    }

    public function messagesGetLongPollServer()
    {
        $request_params = [ // подключаем к полл серверу
            'access_token' => $this->token,
            'v' => $this->version,
            'need_pts' => 1,
            'user_id' => $this->id,
            'lp_version' => 3
        ];

        $url = "https://api.vk.com/method/messages.getLongPollServer";

        return $this->curlExec($url, $request_params);
    }

    public function groupsGetLongPollServer()
    {
        $request_params = [ // подключаем к полл серверу
            'access_token' => $this->token,
            'v' => $this->version,
            'group_id' => $this->id
        ];

        $url = "https://api.vk.com/method/groups.getLongPollServer";

        return $this->curlExec($url, $request_params);
    }

    public function messagesGetConversations($offset = 0, $count = 100, $filter = 'all')
    {
        $request_params = [
            'offset' => $offset,
            'count' => $count,
            'filter' => $filter,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.getConversations';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesDelete($id, $spam = 0, $group_id = null, $delete_for_all = 1)
    {
        $request_params = [
            'message_ids' => $id,
            'spam' => $spam,
            'group_id' => $group_id,
            'delete_for_all' => $delete_for_all,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.delete';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesAddChatUser($chat_id, $user_id, $visible_messages_count = 250)
    {
        $request_params = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'visible_messages_count' => $visible_messages_count,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.addChatUser';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesSend($peer, $mes, $attach = '', $forward = '', $keyboard = false, $disable_mentions = 0)
    {
        if(mb_strlen($mes) > 4000)
        {
            $middle = strrpos(substr($mes, 0, floor(strlen($mes) / 2)), ' ') + 1;
            $string1 = substr($mes, 0, $middle);
            $string2 = substr($mes, $middle);
            $this->messagesSend($peer, $string1, $attach, $forward, $keyboard, $disable_mentions);
            return $this->messagesSend($peer, $string2, $attach, $forward, $keyboard, $disable_mentions);
        }
        $request_params = [
            'random_id' => mt_rand(20, 99999999),
            'peer_id' => $peer,
            'domain' => $this->domain,
            'message' => $mes,
            'disable_mentions' => $disable_mentions,
            'v' => $this->version,
            'access_token' => $this->token,
            'attachment' => $attach,
            'forward_messages' => $forward
        ];

        if ($keyboard)
        {
            $request_params["keyboard"] = json_encode($keyboard);
        }

        $url_mes = 'https://api.vk.com/method/messages.send';

        return $this->curlExec($url_mes, $request_params);
    }

    /**
     * @param $user_ids
     * @param string $name_case
     * именительный – nom
     * родительный – gen
     * дательный – dat
     * винительный – acc
     * творительный – ins
     * предложный – abl
     * @param string $fields
     * @return mixed
     */
    public function usersGet($user_ids, string $name_case = 'nom', string $fields = 'photo_id, verified, sex, bdate, city, country, home_town, has_photo, photo_50, photo_100, photo_200_orig, photo_200, photo_400_orig, photo_max, photo_max_orig, online, domain, has_mobile, contacts, site, education, universities, schools, status, last_seen, followers_count, occupation, nickname, relatives, relation, personal, connections, exports, activities, interests, music, movies, tv, books, games, about, quotes, can_post, can_see_all_posts, can_see_audio, can_write_private_message, can_send_friend_request, is_favorite, is_hidden_from_feed, timezone, screen_name, maiden_name, crop_photo, is_friend, friend_status, career, military, blacklisted, blacklisted_by_me, can_be_invited_group')
    {
        $request_params = [
            'user_ids' => $user_ids,
            'fields' => $fields,
            'name_case' => $name_case,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/users.get';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesGetConversationsById($peer_ids, $extended = 0)
    {
        $request_params = [
            'peer_ids' => $peer_ids,
            'extended' => $extended,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.getConversationsById';

        return $this->curlExec($url_mes, $request_params);
    }

    /*
type:
post — запись на стене пользователя или группы;
comment — комментарий к записи на стене;
photo — фотография;
audio — аудиозапись;
video — видеозапись;
note — заметка;
market — товар;
photo_comment — комментарий к фотографии;
video_comment — комментарий к видеозаписи;
topic_comment — комментарий в обсуждении;
market_comment — комментарий к товару;
sitepage — страница сайта, на котором установлен виджет «Мне нравится»
     */
    public function likesGetList($type, $owner_id, $item_id, $offset = 0, $count = 100, $filter = 'likes')
    {
        $request_params = [
            'type' => $type,
            'owner_id' => $owner_id,
            'item_id' => $item_id,
            'filter' => $filter,
            'extended' => '1',
            'offset' => $offset,
            'count' => $count,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/likes.getList';

        return $this->curlExec($url_mes, $request_params);
    }
    
    public function wallGet($id, $offset = 0, $count = 100)
    {
        $request_params = [
            'owner_id' => $id,
            'domain' => $this->domain,
            'offset' => $offset,
            'count' => $count,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/wall.get';

        return $this->curlExec($url_mes, $request_params);
    }

    public function wallSearch($id, $search, $offset = 0, $count = 100) //нельзя юзать если сообщество
    {
        $request_params = [
            'owner_id' => $id,
            'query' => $search,
            'domain' => '',
            'offset' => $offset,
            'count' => $count,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/wall.search';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesSetActivity($type, $peer_id)
    {
        $request_params = [
            'user_id' => $this->id,
            'type' => $type,
            'peer_id' => $peer_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.setActivity';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesGetConversationMembers($peer_id, $offset = 0, $count = 200)
    {
        $request_params = [
            'peer_id' => $peer_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.getConversationMembers';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesRemoveChatUser($peer_id, $user_id)
    {
        $request_params = [
            'chat_id' => ($peer_id-2000000000),
            'member_id' => $user_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.removeChatUser';

        return $this->curlExec($url_mes, $request_params);
    }

    public static function authorize($version, $id = null, $user = true): string
    {
        $request_params = [
            'client_id' => 6663941,
            'redirect_uri' => 'https://oauth.vk.com/blank.html',
            'display' => 'page',
            'response_type' => 'token',
            'v' => $version
        ];

        if (!$user)
        {
            $request_params["group_ids"] = $id;
            $request_params['scope'] = implode(',', self::scopesGroup);
        }
        else
            $request_params['scope'] = implode(',', self::scopes);
        return 'https://oauth.vk.com/authorize?' . http_build_query($request_params);
    }

    public function groupsGetBanned($owner_id, $offset = 0, $count = 20, $fields = null)
    {
        $request_params = [
            'group_id' => $this->id,
            'offset' => $offset,
            'count' => $count,
            'fields' => $fields,
            'owner_id' => $owner_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/groups.getBanned';

        return $this->curlExec($url_mes, $request_params);
    }

    public function groupsUnBan($user_id)
    {
        $request_params = [
            'group_id' => 201657154,
            'owner_id' => $user_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/groups.unban';

        return $this->curlExec($url_mes, $request_params);
    }

    public function docsSearch($user_text, $offset, $count, $search_own = 0)
    {
        $request_params = [
            'q' => $user_text,
            'search_own' => $search_own,
            'count' => $count,
            'offset' => $offset,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/docs.search';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesGetInviteLink($peer_id, $reset = 0, $group_id = 1)
    {
        $request_params = [
            'peer_id' => $peer_id,
            'reset' => $reset,
            'group_id' => $group_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.getInviteLink';

        return $this->curlExec($url_mes, $request_params);
    }

    public function groupsGetById($group_ids, $fields = null)
    {
        $request_params = [
            'group_ids' => $group_ids,
            'fields' => $fields,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/groups.getById';

        return $this->curlExec($url_mes, $request_params);
    }

    public function messagesGetByConversationMessageId($peer_id, $conversation_message_ids, $extended = null, $fields = null, $group_id = null)
    {
        $request_params = [
            'peer_id' => $peer_id,
            'conversation_message_ids' => $conversation_message_ids,
            'extended' => $extended,
            'fields' => $fields,
            'group_id' => $group_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/messages.getByConversationMessageId';

        return $this->curlExec($url_mes, $request_params);
    }

    public function photosGetOwnerPhotoUploadServer($owner_id)
    {
        $request_params = [
            'owner_id' => $owner_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/photos.getOwnerPhotoUploadServer';

        return $this->curlExec($url_mes, $request_params);
    }

    public function photosSaveOwnerPhoto($server, $hash, $photo)
    {
        $request_params = [
            'server' => $server,
            'hash' => $hash,
            'photo' => $photo,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/photos.saveOwnerPhoto';

        return $this->curlExec($url_mes, $request_params);
    }

    public function wallDelete($owner_id, $post_id)
    {
        $request_params = [
            'owner_id' => $owner_id,
            'post_id' => $post_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/wall.delete';

        return $this->curlExec($url_mes, $request_params);
    }

    public function photosGetAll($owner_id, $offset = 0, $count = 100)
    {
        $request_params = [
            'owner_id' => $owner_id,
            'count' => $count,
            'offset' => $offset,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/photos.getAll';

        return $this->curlExec($url_mes, $request_params);
    }

    public function photosDelete($owner_id, $photo_id)
    {
        $request_params = [
            'owner_id' => $owner_id,
            'photo_id' => $photo_id,
            'v' => $this->version,
            'access_token' => $this->token
        ];

        $url_mes = 'https://api.vk.com/method/photos.delete';

        return $this->curlExec($url_mes, $request_params);
    }

    public static function uploadPhotoUrl($url, $photo, $square_crop = '')
    {
        if (function_exists('curl_file_create')) {
            $cFile = curl_file_create($photo);
        } else {
            $cFile = '@' . realpath($photo);
        }
        $post['photo'] = $cFile;
        if($square_crop != '')
        {
            $post['_square_crop'] = $square_crop;
        }
        $ch = curl_init();
        $headers = [
            'Content-Type: multipart/form-data'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
        return json_decode($server_output, true);
    }
}