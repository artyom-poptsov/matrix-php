<?php

namespace matrix;

include_once "common.php";

class Session {
    private $server_location;
    private $user_id;
    private $access_token;

    public function __construct($server_location, $user_id, $access_token) {
        $this->server_location = $server_location;
        $this->user_id         = $user_id;
        $this->access_token    = $access_token;
    }

    public function get_user_id() {
        return $this->user_id;
    }
    public function get_access_token() {
        return $this->access_token;
    }

    public function get_server_location() {
        return $this->server_location;
    }

    /**
     * Create a new room.
     * @param $name
     * @return a new room object;
     * @throws Matrix_exception on errors.
     */
    public function create_room($name) {
        $curl = curl_init();
        $request_data = [ "room_alias_name" => $name ];

        curl_setopt($curl, CURLOPT_URL,
                    $this->server_location . MATRIX_CLIENT_URL . '/createRoom'
                    . '?access_token=' . $this->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json'));

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            if ($json['errcode']) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            var_dump($json);
            return new Room($json['room_alias'], $json['room_id']);
        } else {
            throw new Matrix_exception("Could not create a room: " . $result);
        }
    }

    /**
     * Get all the user's state.
     *
     * @return User state.
     * @throws Matrix_exception on errors.
     */
    public function sync() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,
                    $this->server_location . MATRIX_CLIENT_URL . '/sync'
                    . "?access_token=" . $this->access_token);
        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json;
        } else {
            throw new Matrix_exception("Could not synchronize with a server");
        }
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
        $curl = curl_init();

        $request_data = [
            'msgtype' => $type,
            'body'    => $body
        ];

        curl_setopt($curl, CURLOPT_URL,
                    $this->server_location . MATRIX_CLIENT_URL . '/rooms/'
                    . $room->get_id() . '/send/m.room.message'
                    . '?access_token=' . $this->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json'));

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            return json_decode($result, true)['event_id'];
        } else {
            throw new Matrix_exception("Could not send a message");
        }
    }

    /**
     * ADMIN ONLY: Check if a username is available for registration.
     *
     * XXX: This only works with Synapse Matrix server.
     *
     * @param $username Username to check.
     * @return true if username is available, false otherwise.
     * @throws Matrix_exception on errors.
     */
    public function is_username_available($username) {
        $json = $this->get_user_info($username);
        return $json == NULL;
    }

    /**
     * ADMIN ONLY: Get information about a specific user.
     *
     * XXX: This only works with Synapse Matrix server.
     *
     * @param $username Username to use.
     * @return JSON with user information; NULL if user does not exist.
     * @throws Matrix_exception on errors.
     */
    public function get_user_info($username) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_URL, $this->server_location
                    . SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION
                    . '/users/' . $username
                    . "?access_token=" . $this->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            var_dump($json);
            if (array_key_exists('errcode', $json)) {
                if ($json['errcode'] == 'M_NOT_FOUND') {
                    return NULL;
                }
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json;
        } else {
            throw new Matrix_exception($json['errcode'], $json['error']);
        }
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
        $curl = curl_init();

        $request_data = [
            'new_password' => $new_password
        ];

        curl_setopt($curl, CURLOPT_URL,
                    $this->server_location . MATRIX_CLIENT_URL . '/account/password'
                    . '?access_token=' . $this->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json'));
        $result = curl_exec($curl);

        $json = null;
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
        } else {
            throw new Matrix_exception("Could not change a password");
        }

        $request_data = [
            'auth' => [ 
                'type' => 'm.login.password',
                'user' => $this->user_id,
                'password' => $old_password,
                'session'  => $json['session']
            ],
        ];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
        $result = curl_exec($curl);

        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json;
        } else {
            throw new Matrix_exception("Could not change a password");
        }
    }
}
