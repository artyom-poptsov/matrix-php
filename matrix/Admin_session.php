<?php

namespace matrix;

require_once(dirname(__FILE__) . "/core/types/ID.php");

use matrix\core\types\ID;

/**
 * A Matrix administrator session that can be derived from a regular session by
 * using 'Session::sudo' method.
 *
 * Note that an Admin session obtained with 'Session::sudo' shares the access
 * token with the parent session, so you must call either 'Session::logout' or
 * 'Admin_session::logout' to log out from the server.
 *
 * XXX: Most of the methods here are only applicable to a specific Matrix
 *      implementation (Synapse.)
 *
 * @see Session::sudo
 */
class Admin_session extends Session {

    /**
     * The main constructor of the class that builds an Admin_session instance
     * based on a given Session instance. Make sure that given session belongs
     * to a user with admin rights.
     *
     * @param Session $session An administrator session instance.
     */
    public function __construct($session) {
        parent::__construct($session->get_matrix_client(),
                            $session->get_user_id(),
                            $session->get_access_token());
    }

    /**
     * Check if a username is available for registration.
     *
     * @param  string $username Username to check.
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
     * @param ID $username Username to use.
     * @return JSON with user information; NULL if user does not exist.
     * @throws Matrix_exception on errors.
     */
    public function get_user_info(ID $id) {
        try {
            return $this->matrix_client->get(
                SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION . '/users/'
                . $id->to_string(),
                [ "access_token" => $this->access_token ]);
        } catch(Matrix_exception $e) {
            if ($e->get_error_code() === 'M_NOT_FOUND') {
                return NULL;
            }
            throw $e;
        }
    }

    /**
     * Check if a user has admin rights.
     *
     * @param  ID $user_id ID of the user to check.
     * @return true if the user has admin rights, false otherwise.
     */
    public function is_admin(ID $user_id) {
        return $this->get_user_info($user_id)['admin'] == 1;
    }

    /**
     * Reset the password of the specified user.
     *
     * @param ID      $user_id Fully qualified ID of the user.
     * @param string  $new_password New password to set.
     * @param boolean $logout_devices Should the user be logged out from all
     *     devices? Defaults to 'true'.
     */
    public function reset_password($user_id, $new_password,
                                   $logout_devices = true) {
        $this->matrix_client->post(
            SYNAPSE_URL . 'admin/v1/reset_password/' . $user_id->to_string(),
            [
                'new_password'   => $new_password,
                'logout_devices' => $logout_devices
            ],
            [ 'access_token' => $this->access_token ]
        );
    }

    /**
     * Deactivate an account.
     *
     * @param ID      $user_id ID of the user that should be deactivated.
     * @param boolean $should_erase Marks the user as GDPR-erased[1], if set to
     *     'true'. Defaults to 'false'.
     *
     * [1] General Data Protection Regulation (GDPR):
     *     https://en.wikipedia.org/wiki/General_Data_Protection_Regulation
     */
    public function deactivate_account($user_id, $should_erase = false) {
        $this->matrix_client->post(
            SYNAPSE_URL . 'admin/v1/deactivate/' . $user_id->to_string(),
            [ 'erase'        => $should_erase ],
            [ 'access_token' => $this->access_token ]
        );
    }
}

?>

