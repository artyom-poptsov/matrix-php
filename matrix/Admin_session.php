<?php

namespace matrix;

class Admin_session extends Session {
    public function __construct($session) {
        parent::__construct($session->get_server_location(),
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
        $curl = curl_init();
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
}

?>

