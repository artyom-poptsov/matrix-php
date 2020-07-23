<?php

declare(strict_types = 1);

namespace matrix;

require_once(dirname(__FILE__) . "/core/types/ID.php");
require_once(dirname(__FILE__) . "/common.php");
require_once(dirname(__FILE__) . "/Admin_session.php");

use matrix\core\types\Content_URI;
use matrix\core\types\ID;

/**
 * A Matrix session.
 *
 * Note that you should explicitly call 'logout' method to finish the session.
 */
class Session {
    /**
     * Matrix client instance.
     */
    protected Matrix_client $matrix_client;

    protected ID $user_id;
    protected ?string $access_token;

    public function __construct(Matrix_client $matrix_client,
                                ID $user_id,
                                string $access_token) {
        $this->matrix_client   = $matrix_client;
        $this->user_id         = $user_id;
        $this->access_token    = $access_token;
    }

    /**
     * Get the USER ID.
     *
     * @return ID User ID.
     */
    public function get_user_id() : ID {
        return $this->user_id;
    }

    /**
     * Get the access token.
     *
     * @return string The access token (can be NULL.)
     */
    public function get_access_token() : ?string {
        return $this->access_token;
    }

    /**
     * Get the Matrix client instance.
     *
     * @return string The Matrix client instance.
     */
    public function get_matrix_client() : Matrix_client {
        return $this->matrix_client;
    }

    /**
     * Get the server location.
     *
     * @return string The server location.
     */
    public function get_server_location() : string {
        return $this->matrix_client->get_server();
    }

    /**
     * Try to acquire admin rights.
     *
     * @return Admin_session Administrator session instance on success.
     * @throws Matrix_exception with errcode 'M_FORBIDDEN' if the user has
     *     insufficient rights.
     */
    public function sudo() : Admin_session {
        $admin_session = new Admin_session($this);
        // Check user rights.
        $admin_session->is_admin($this->user_id);
        return $admin_session;
    }

    /**
     * Return information about the owner of the current access token.
     *
     * @return JSON response.
     */
    public function whoami() {
        return $this->matrix_client->get(
            MATRIX_CLIENT_URL . '/account/whoami',
            [ 'access_token' => $this->access_token ]
        );
    }

    /**
     * Get avatar Content URI.
     *
     * @return Content_URI Avatar URI.
     */
    public function get_avatar() : Content_URI {
        $json = $this->matrix_client->get(
            MATRIX_CLIENT_URL . '/profile/' . $this->user_id->to_string()
            . '/avatar_url',
            [ 'access_token' => $this->access_token ]
        );
        return new Content_URI($json['avatar_url']);
    }

    /**
     * Set avatar by a Content URI.
     *
     * @param Content_URI Avatar URI.
     * @throws Matrix_exception on errors.
     */
    public function set_avatar(Content_URI $uri) : void {
        $json = $this->matrix_client->put(
            MATRIX_CLIENT_URL . '/profile/' . $this->user_id->to_string()
            . '/avatar_url',
            [ 'avatar_url'   => $uri->to_string() ],
            [ 'access_token' => $this->access_token ]
        );
    }

    /**
     * Create a new room.
     * @param string $name A room name.
     * @return Room A new room instance.
     * @throws Matrix_exception on errors.
     */
    public function create_room(string $name) : Room {
        $json = $this->matrix_client->post(MATRIX_CLIENT_URL . '/createRoom',
                                           [ "room_alias_name" => $name ],
                                           [ 'access_token' => $this->access_token ]);
        return new Room(new ID($json['room_alias']), new ID($json['room_id']));
    }

    /**
     * Get all the user's state.
     *
     * @return User state.
     * @throws Matrix_exception on errors.
     */
    public function sync() {
        return $this->matrix_client->get(
            MATRIX_CLIENT_URL . '/sync',
            [ 'access_token' => $this->access_token ]);
    }

    /**
     * Send a message.
     *
     * @param $room A Room instance.
     * @param $type Message type (e.g. 'm.text'.)
     * @param $body Message body.
     * @return Unique event ID that identifies the sent message.
     * @throws Matrix_exception on errors.
     */
    public function send_message(Room $room, string $type, string $body) {
        return $this->matrix_client->post(
            MATRIX_CLIENT_URL . '/rooms/' . $room->get_id()->to_string()
            . '/send/m.room.message',
            [
                'msgtype' => $type,
                'body'    => $body
            ],
            [ 'access_token' => $this->access_token ]
        );
    }

    /**
     * Get list of joined rooms.
     *
     * @return array Array of joined rooms.
     * @throws Matrix_exception on errors.
     */
    public function get_joined_rooms() : array {
        return $this->matrix_client->get(
            MATRIX_CLIENT_URL . '/joined_rooms',
            [ 'access_token' => $this->access_token ]
        )['joined_rooms'];
    }

    /**
     * Join a room.
     *
     * @param object $room A room ID, alias or Room class instance.
     * @param array $third_party_signed If the parameter was supplied,
     *     the homeserver must verify that it matches a pending
     *     'm.room.third_party_invite' event in the room, and perform key validity
     *     checking if required by the event.
     * @return string ID of a joined room.
     * @throws Matrix_exception on errors.
     */
    public function join_room($room, array $third_party_signed = []) {
        if ($room instanceof Room) {
            $room = $room->get_id()->to_string();
        }

        if (! empty($third_party_signed)) {
            $third_party_signed
                = [ 'third_party_signed' => $third_party_signed ];
        }

        return $this->matrix_client->post(
            MATRIX_CLIENT_URL . '/join/' . $room,
            $third_party_signed,
            [ 'access_token' => $this->access_token ]
        )['room_id'];
    }

    /**
     * Change user password.
     *
     * XXX: This only works when user's old password is needed for the 2nd stage
     *      of the authentication.
     *
     * @param $old_password Old user password.
     * @param $new_password New user password.
     */
    public function change_password(string $old_password,
                                    string $new_password) : void {
        $json = $this->matrix_client->post(
            MATRIX_CLIENT_URL . '/account/password',
            [ 'new_password' => $new_password ],
            [ 'access_token' => $this->access_token ]
        );

        $this->matrix_client->post(
            MATRIX_CLIENT_URL . '/account/password',
            [
                'auth' => [
                    'type'     => 'm.login.password',
                    'user'     => $this->user_id,
                    'password' => $old_password,
                    'session'  => $json['session']
                ],
            ],
            [ 'access_token' => $this->access_token ]
        );
    }

    /**
     * End the current session by invalidating the access token. Please note
     * that you cannot use this session after calling this method.
     *
     * @throws Matrix_exception on errors.
     */
    public function logout() : void {
        if ($this->access_token != NULL) {
            $this->matrix_client->post(
                MATRIX_CLIENT_URL . '/logout',
                [ ],
                [ 'access_token' => $this->access_token ]
            );
            $this->access_token = NULL;
        }
    }
}
