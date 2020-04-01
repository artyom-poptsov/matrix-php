<?php declare(strict_types=1);

include "matrix/Session.php";

use PHPUnit\Framework\TestCase;
use \matrix\Session;

final class SessionTest extends TestCase {
    public function test_session_creation(): void {
        $server_location = 'https://homeserver';
        $user_id         = '@alice:homeserver';
        $access_token    = 'secret-token';
        $session = new Session($server_location, $user_id, $access_token);
        $this->assertEquals($session->get_server_location(), $server_location);
        $this->assertEquals($session->get_user_id(),         $user_id);
        $this->assertEquals($session->get_access_token(),    $access_token);
    }
}
