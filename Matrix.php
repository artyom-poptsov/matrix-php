<?php

namespace matrix;

include "Matrix_exception.php";
include "Room.php";
include "Session.php";
include "common.php";

//// Helper procedures.

function make_mac($shared_secret, $data=[]) {
    var_dump(join(chr(0), $data));
    return hash_hmac('sha1', join(chr(0), $data), $shared_secret);
}

//// Classes.

class Matrix {
    private $server_location;
    private $shared_secret;

    /**
     * Matrix class constructor.
     * @param $server_location URL of the Matrix endpoint.
     * @param $shared_secret   A shared secret from the 'homeserver.yaml'.
     */
    function __construct($server_location, $shared_secret) {
        $this->server_location = $server_location;
        $this->shared_secret   = $shared_secret;
    }

    /**
     * Get 'nonce' hash from a server.
     *
     * @return String a 'nonce' value.
     * @throws Matrix_exception on errors
     */
    public function request_nonce() {
        $url  = $this->server_location . MATRIX_REGISTER_URL;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        if ($response) {
            $json = json_decode($response, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json['nonce'];
        } else {
            throw new Matrix_exception("Could not make a nonce request.");
        }
    }

    /**
     * Register a new user with the specified password.
     * @param $user User name to use.
     * @param $password A password to use.
     * @param $is_admin Should we grant admin rights to the new user? (false by default.)
     * @param $user_type Type of the new user.
     * @throws Matrix_exception on errors.
     */
    public function request_registration($user, $password, $is_admin = false, $user_type = '') {
        $nonce       = $this->request_nonce();
        $data_array  = [ $nonce, $user, $password ];
        $data_array[] = $is_admin ? 'admin' : 'notadmin';
        if ($user_type != '') {
            $data_array[] = $user_type;
        }

        $mac = make_mac($this->shared_secret, $data_array);

        var_dump($mac);

        $request_data = [
            'nonce'     => $nonce,
            'username'  => $user,
            'password'  => $password,
            'mac'       => $mac,
            'admin'     => $is_admin,
        ];
        if ($user_type != '') {
            $request_data[] = $user_type;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->server_location . MATRIX_REGISTER_URL);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json;
        } else {
            throw new Matrix_exception("Could not create a user.");
        }
    }

    /**
     * Get available login methods for the server.
     * @return An assoctiative array.
     * @throws Matrix_exception on errors.
     */
    public function get_available_login_methods() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->server_location . MATRIX_CLIENT_URL . '/login');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 0);

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json;
        } else {
            throw new Matrix_exception("Could not execute request");
        }
    }

    /**
     * Try to authenticate with the server.
     *
     * @param $type Authentication type.
     * @param $user Username to use.
     * @param $password Password to use.
     * @return A new Session object.
     */
    public function login($type, $user, $password) {
        $curl = curl_init();

        $request_data = [
            'type'     => $type,
            'user'     => $user,
            'password' => $password
        ];

        curl_setopt($curl, CURLOPT_URL,
                    $this->server_location . MATRIX_CLIENT_URL . '/login');
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));

        $result = curl_exec($curl);
        curl_close($curl);
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return new Session($this->server_location, $json['user_id'], $json['access_token']);
        } else {
            throw new Matrix_exception("Could not authenticate");
        }
    }
}

?>
