<?php

namespace matrix;

include_once "Matrix_exception.php";
include_once "Room.php";
include_once "Session.php";
include_once "common.php";
include_once "Matrix_client.php";

//// Helper procedures.

function make_mac($shared_secret, $data=[]) {
    return hash_hmac('sha1', join(chr(0), $data), $shared_secret);
}

//// Classes.

class Matrix {
    /**
     * Matrix client instance.
     */
    private $matrix_client;

    /**
     * Shared secret for your server as a string.
     */
    private $shared_secret;

    /**
     * Matrix class constructor.
     * @param $server_location URL of the Matrix endpoint.
     * @param $shared_secret   A shared secret from the 'homeserver.yaml'.
     */
    function __construct($server_location, $shared_secret) {
        $this->matrix_client = new Matrix_client($server_location);
        $this->shared_secret   = $shared_secret;
    }

    /**
     * Set debug mode.
     *
     * @param $is_enabled Is debug mode enabled?
     */
    public function set_debug_mode($is_enabled) {
        $this->matrix_client->set_debug_mode($is_enabled);
    }

    /**
     * Make a user fully qualified name (FQN) on the current server based on
     * $name.
     *
     * @param @name A name to use.
     * @return A fully qualified name string.
     */
    public function make_fqn($name) {
        return make_fqn($name, $this->matrix_client->get_server());
    }

    /**
     * Get 'nonce' hash from a server.
     *
     * @return String a 'nonce' value.
     * @throws Matrix_exception on errors
     */
    public function request_nonce() {
        return $this->matrix_client->get(MATRIX_REGISTER_URL)['nonce'];
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
        $nonce        = $this->request_nonce();
        $data_array   = [ $nonce, $user, $password ];
        $data_array[] = $is_admin ? 'admin' : 'notadmin';
        if ($user_type != '') {
            $data_array[] = $user_type;
        }

        $mac = make_mac($this->shared_secret, $data_array);

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

        return $this->matrix_client->post(MATRIX_REGISTER_URL, $request_data);
    }

    /**
     * Get available login methods for the server.
     * @return An assoctiative array.
     * @throws Matrix_exception on errors.
     */
    public function get_available_login_methods() {
        return $this->matrix_client->get(MATRIX_CLIENT_URL . '/login');
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
        $request_data = [
            'type'     => $type,
            'user'     => $user,
            'password' => $password
        ];

        $json = $this->matrix_client->post(MATRIX_CLIENT_URL . '/login',
                                           $request_data);
        return new Session($this->matrix_client,
                           $json['user_id'],
                           $json['access_token']);
    }
}

?>
