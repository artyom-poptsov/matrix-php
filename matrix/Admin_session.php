<?php

namespace matrix;

class Admin_session extends Session {
    public function __construct($session) {
        parent::__construct($session->get_matrix_client(),
                            $session->get_user_id(),
                            $session->get_access_token());
    }

    /**
     * Check if a username is available for registration.
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
     * Get information about a specific user.
     *
     * XXX: This only works with Synapse Matrix server.
     *
     * @param $username Username to use.
     * @return JSON with user information; NULL if user does not exist.
     * @throws Matrix_exception on errors.
     */
    public function get_user_info($username) {
        $json = $this->matrix_client->get(
            SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION . '/users/'
            . $username,
            [ "access_token" => $this->access_token ]);
        return ($json['errcode'] == 'M_NOT_FOUND') ? NULL : $json;
    }

    /**
     * Check if a user has admin rights.
     *
     * @param $user_id ID of the user to check.
     * @return true if the user has admin rights, false otherwise.
     */
    public function is_admin($user_id) {
        return $this->get_user_info($user_id)['admin'] == 1;
    }

    /**
     * Reset the password of the specified user.
     *
     * @param $user_id Fully qualified ID of the user.
     * @param $new_password New password to set.
     * @param $logout_devices Should the user be logged out from all devices?
     *     Defaults to 'true'.
     */
    public function reset_password($user_id, $new_password,
                                   $logout_devices = true) {
        $this->matrix_client->post(
            SYNAPSE_URL . 'admin/v1/' . 'reset_password/' . $user_id,
            [
                'new_password'   => $new_password,
                'logout_devices' => $logout_devices
            ],
            [ 'access_token' => $this->access_token ]
        );
    }
}

?>

