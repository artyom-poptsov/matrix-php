<?php

//// Constants.

// Matrix constants.
define("MATRIX_REGISTER_URL", "/_matrix/client/r0/admin/register");
define("MATRIX_CLIENT_URL",   "/_matrix/client/r0");

// Synapse-specific constants.
define("SYNAPSE_URL", "/_synapse/");
define("SYNAPSE_API_VERSION", "v2");

/**
 * Check if a $name is a fully qualified name (FQN.)  E.g.
 *     '@avp:example.ru'
 *
 * @param $name Name to check.
 * @return true if $name is a fully qualified user name, false otherwise.
 */
function is_fqn($name) {
    return preg_match('/@.*:.*/', $user_id) > 0;
}

/**
 * Make Matrix fully qualified name (FQN.)
 *
 * @param $name Name to use.
 * @param $server Server name.
 * @return A fully qualified name string.
 */
function make_fqn($name, $server) {
    return '@' . $name . ':' . $server;
}



?>
