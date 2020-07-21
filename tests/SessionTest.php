<?php declare(strict_types=1);

include_once "matrix/Session.php";
include_once "matrix/Content_URI.php";
include_once "matrix/Room.php";
include_once "matrix/Matrix_client.php";
include_once "matrix/common.php";

use PHPUnit\Framework\TestCase;
use \matrix\Session;
use \matrix\Content_URI;
use \matrix\Room;
use \matrix\Matrix_client;

final class SessionTest extends TestCase {
    public function test_session_creation(): void {
        $server_location = 'https://homeserver';
        $user_id         = '@alice:homeserver';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('get_server')
                      ->willReturn($server_location);

        $session = new Session($matrix_client, $user_id, $access_token);
        $this->assertEquals($session->get_server_location(), $server_location);
        $this->assertEquals($session->get_user_id(),         $user_id);
        $this->assertEquals($session->get_access_token(),    $access_token);
        $this->assertEquals($session->get_matrix_client(),   $matrix_client);
    }

    public function test_logout(): void {
        $server_location = 'https://example.org/';
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $matrix_client = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(
                          MATRIX_CLIENT_URL . '/logout',
                          [ ],
                          [ 'access_token' => $access_token ]
                      );

        $session = new Session($matrix_client, $user_id, $access_token);
        $room    = $session->logout();
    }

    public function test_create_room(): void {
        $server_location = 'https://example.org/';
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $matrix_client = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(
                          MATRIX_CLIENT_URL . '/createRoom',
                          [ 'room_alias_name' => $room_alias ],
                          [ 'access_token'    => $access_token ]
                      )
                      ->willReturn(
                          [
                              'room_alias' => $room_alias,
                              'room_id'    => '#' . $room_alias . 'example.org'
                          ]
                      );

        $session = new Session($matrix_client, $user_id, $access_token);
        $room    = $session->create_room($room_alias);
    }

    public function test_whoami(): void {
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(MATRIX_CLIENT_URL . '/account/whoami',
                             [ 'access_token'    => $access_token ]);

        $session = new Session($matrix_client, $user_id, $access_token);
        $room    = $session->whoami();
    }

    public function test_sync(): void {
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(MATRIX_CLIENT_URL . '/sync',
                             [ 'access_token'    => $access_token ]);

        $session = new Session($matrix_client, $user_id, $access_token);
        $session->sync();
    }

    public function test_send_message(): void {
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $msg_type        = "m.text";
        $msg_body        = "hello world";
        $matrix_client   = $this->createMock(Matrix_client::class);
        $room            = $this->createMock(Room::class);

        $room->expects($this->once())
             ->method('get_id')
             ->willReturn($room_alias);

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(
                          MATRIX_CLIENT_URL . '/rooms/' . $room_alias
                          . '/send/m.room.message',
                          [
                              'msgtype' => $msg_type,
                              'body'    => $msg_body
                          ],
                          [ 'access_token'    => $access_token ]
                      );

        $session = new Session($matrix_client, $user_id, $access_token);
        $room    = $session->send_message($room, $msg_type, $msg_body);
    }

    public function test_change_password(): void {
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $old_password    = 'passw0rd';
        $new_password    = 'passw1rd';
        $session         = 'session-test';
        $matrix_client   = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->exactly(2))
                      ->method('post')
                      ->withConsecutive(
                          [
                              MATRIX_CLIENT_URL . '/account/password',
                              [ 'new_password' => $new_password ],
                              [ 'access_token' => $access_token ]
                          ],
                          [
                              MATRIX_CLIENT_URL . '/account/password',
                              [
                                  'auth' => [
                                      'type'     => 'm.login.password',
                                      'user'     => $user_id,
                                      'password' => $old_password,
                                      'session'  => $session
                                  ],
                              ],
                              [ 'access_token' => $access_token ]
                          ]
                      )
                      ->willReturn([ 'session' => $session ]);
        $session = new Session($matrix_client, $user_id, $access_token);
        $session->change_password($old_password, $new_password);
    }

    public function test_get_avatar_url(): void {
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $session         = 'session-test';
        $avatar_url      = 'mxc://matrix.example.org/avatar';
        $matrix_client   = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(
                          MATRIX_CLIENT_URL . '/profile/' . $user_id . '/avatar_url',
                          [ 'access_token' => $access_token ]
                      )
                      ->willReturn([ 'avatar_url' => $avatar_url ]);
        $session = new Session($matrix_client, $user_id, $access_token);
        $url = $session->get_avatar_url();
        $this->assertEquals($url->to_string(), $avatar_url);
    }

    public function test_set_avatar_url(): void {
        $user_id         = '@alice:example.org/';
        $access_token    = 'secret-token';
        $room_alias      = "test-room";
        $session         = 'session-test';
        $avatar_url      = new Content_URI('mxc://matrix.example.org/avatar');
        $matrix_client   = $this->createMock(Matrix_client::class);

        $matrix_client->expects($this->once())
                      ->method('put')
                      ->with(
                          MATRIX_CLIENT_URL . '/profile/' . $user_id . '/avatar_url',
                          [ 'avatar_url'   => 'mxc://matrix.example.org/avatar' ],
                          [ 'access_token' => $access_token ]
                      );
        $session = new Session($matrix_client, $user_id, $access_token);
        $session->set_avatar_url($avatar_url);
    }

}
