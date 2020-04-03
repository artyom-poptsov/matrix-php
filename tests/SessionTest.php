<?php declare(strict_types=1);

include_once "matrix/Session.php";
include_once "matrix/Matrix_client.php";
include_once "matrix/common.php";

use PHPUnit\Framework\TestCase;
use \matrix\Session;
use \matrix\Matrix_client;

final class SessionTest extends TestCase {
    public function test_session_creation(): void {
        $server_location = 'https://homeserver';
        $user_id         = '@alice:homeserver';
        $access_token    = 'secret-token';
        $matrix_client = new Matrix_client($server_location);
        $session = new Session($matrix_client, $user_id, $access_token);
        $this->assertEquals($session->get_server_location(), $server_location);
        $this->assertEquals($session->get_user_id(),         $user_id);
        $this->assertEquals($session->get_access_token(),    $access_token);
        $this->assertEquals($session->get_matrix_client(),   $matrix_client);
    }

    public function test_create_room(): void {
        $server_location = 'https://example.org/';
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $matrix_client = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(MATRIX_CLIENT_URL . '/createRoom',
                             [ 'room_alias_name' => $room_alias ],
                             [ 'access_token'    => $access_token ] );

        $session = new Session($matrix_client, $user_id, $access_token);
        $room    = $session->create_room($room_alias);
    }
}
