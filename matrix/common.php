<?php
/**
 * common.php -- Common Matrix constants and procedures.
 *
 * This file contains common Matrix constants and procedures that are used in
 * other files.
 *
 * @package matrix
 * @author  Artyom V. Poptsov <poptsov.artyom@gmail.com>
 */

namespace matrix;

//// Constants.

// Matrix constants.
define("MATRIX_REGISTER_URL", "/_matrix/client/r0/admin/register");
define("MATRIX_CLIENT_URL",   "/_matrix/client/r0");
define('MATRIX_MEDIA_UPLOAD_URL',   '/_matrix/media/r0/upload');
define('MATRIX_MEDIA_DOWNLOAD_URL', '/_matrix/media/r0/download');

// Synapse-specific constants.
define("SYNAPSE_URL", "/_synapse/");
define("SYNAPSE_API_VERSION", "v2");

/**
 * Predicate. Check if a $name is a fully qualified name (FQN.) E.g.
 *     '@avp:example.ru'
 *
 * @param string $name Name to check.
 * @return boolean true if $name is a fully qualified user name, false otherwise.
 */
function is_fqn($name) {
    return preg_match('/@.*:.*/', $name) > 0;
}

/**
 * Make Matrix fully qualified name (FQN.)
 *
 * @param string $name   Name to use.
 * @param string $server Server name.
 * @return string A fully qualified name.
 */
function make_fqn($name, $server) {
    return '@' . $name . ':' . $server;
}



?>
