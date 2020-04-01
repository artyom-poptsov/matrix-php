<?php

namespace matrix;

include "common.php";

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
}
