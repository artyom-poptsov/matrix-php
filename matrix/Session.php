<?php

namespace matrix;

require_once(dirname(__FILE__) . "/common.php");
require_once(dirname(__FILE__) . "/Admin_session.php");

/**
 * A Matrix session.
 *
 * Note that you should explicitly call 'logout' method to finish the session.
 */
class Session {
    /**
     * Matrix client instance.
     */
    protected $matrix_client;

    protected $user_id;
    protected $access_token;

    public function __construct($matrix_client, $user_id, $access_token) {
        $this->matrix_client   = $matrix_client;
        $this->user_id         = $user_id;
        $this->access_token    = $access_token;
    }

    public function get_user_id() {
        return $this->user_id;
    }
    public function get_access_token() {
        return $this->access_token;
    }

    public function get_matrix_client() {
        return $this->matrix_client;
    }

    public function get_server_location() {
        return $this->matrix_client->get_server();
    }

    /**
     * Try to acquire admin rights.
     *
     * @return Admin_session instance on success.
     * @throws Matrix_exception with errcode 'M_FORBIDDEN' if the user has
     *     insufficient rights.
     */
    public function sudo() {
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
     * Get avatar URL.
     *
     * @return string Avatar URL.
     */
    public function get_avatar_url() {
        $json = $this->matrix_client->get(
            MATRIX_CLIENT_URL . '/profile/' . $this->user_id . '/avatar_url',
            [ 'access_token' => $this->access_token ]
        );
        return $json['avatar_url'];
    }

    /**
     * Set avatar URL.
     *
     * @throws Matrix_exception on errors.
     */
    public function set_avatar_url($url) {
        $json = $this->matrix_client->put(
            MATRIX_CLIENT_URL . '/profile/' . $this->user_id . '/avatar_url',
            [ 'avatar_url'   => $url ],
            [ 'access_token' => $this->access_token ]
        );
    }

    /**
     * Create a new room.
     * @param $name
     * @return a new room object;
     * @throws Matrix_exception on errors.
     */
    public function create_room($name) {
        $json = $this->matrix_client->post(MATRIX_CLIENT_URL . '/createRoom',
                                           [ "room_alias_name" => $name ],
                                           [ 'access_token' => $this->access_token ]);
        return new Room($json['room_alias'], $json['room_id']);
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
    public function send_message($room, $type, $body) {
        return $this->matrix_client->post(
            MATRIX_CLIENT_URL . '/rooms/' . $room->get_id() . '/send/m.room.message',
            [
                'msgtype' => $type,
                'body'    => $body
            ],
            [ 'access_token' => $this->access_token ]
        );
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
    public function change_password($old_password, $new_password) {
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
    public function logout() {
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
