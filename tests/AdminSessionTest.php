<?php declare(strict_types=1);

include_once "matrix/Session.php";
include_once "matrix/Admin_session.php";
include_once "matrix/Matrix_exception.php";
include_once "matrix/Room.php";
include_once "matrix/Matrix_client.php";
include_once "matrix/common.php";

use PHPUnit\Framework\TestCase;
use \matrix\Session;
use \matrix\Admin_session;
use \matrix\Room;
use \matrix\Matrix_client;
use \matrix\Matrix_exception;

final class AdminSessionTest extends TestCase {
    public function test_successful_sudo(): void {
        $server_location = 'https://example.org';
        $user_id         = '@alice:example.org';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);
        $session = new Session($matrix_client, $user_id, $access_token);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(
                          SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION
                          . '/users/' . $user_id,
                          [ 'access_token' => $access_token ]
                      )
                      ->willReturn(
                          [ 'admin' => 1 ]
                      );

        $result = $session->sudo();
        $this->assertInstanceOf(Admin_session::class, $result);
    }

    public function test_logout(): void {
        $server_location = 'https://example.org';
        $user_id         = '@alice:example.org';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);
        $session = new Session($matrix_client, $user_id, $access_token);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(
                          SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION
                          . '/users/' . $user_id,
                          [ 'access_token' => $access_token ]
                      )
                      ->willReturn(
                          [ 'admin' => 1 ]
                      );

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(
                          MATRIX_CLIENT_URL . '/logout',
                          [ ],
                          [ 'access_token' => $access_token ]
                      );

        $result = $session->sudo();
        $result->logout();
    }

    public function test_sudo_exception(): void {
        $server_location = 'https://example.org';
        $user_id         = '@alice:example.org';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);
        $session = new Session($matrix_client, $user_id, $access_token);

        $this->expectException(Matrix_exception::class);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(
                          SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION
                          . '/users/' . $user_id,
                          [ 'access_token' => $access_token ]
                      )
                      ->will(
                          $this->throwException(
                              new Matrix_exception('error', '-41')
                          )
                      );

        $session->sudo();
    }

    public function test_get_user_info_returns_null(): void {
        $user_id         = '@alice:example.org';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);
        $session       = $this->createMock(Session::class);

        $matrix_client->expects($this->once())
                      ->method('get')
                      ->with(
                          SYNAPSE_URL . 'admin/' . SYNAPSE_API_VERSION
                          . '/users/' . $user_id,
                          [ 'access_token' => $access_token ]
                      )
                      ->will(
                          $this->throwException(
                              new Matrix_exception('error', 'M_NOT_FOUND')
                          )
                      );

        $session->expects($this->once())
                ->method('get_matrix_client')
                ->willReturn($matrix_client);
        $session->expects($this->once())
                ->method('get_user_id')
                ->willReturn($user_id);
        $session->expects($this->once())
                ->method('get_access_token')
                ->willReturn($access_token);

        $admin_session = new Admin_session($session);
        $result = $admin_session->get_user_info('@alice:example.org');
        $this->assertNull($result);
    }

    public function test_reset_password(): void {
        $user_id         = '@alice:example.org';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);
        $session       = $this->createMock(Session::class);
        $new_password  = 'passw0rd';

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(
                          SYNAPSE_URL . 'admin/v1/reset_password/' . $user_id,
                          [
                              'new_password'   => $new_password,
                              'logout_devices' => true
                          ],
                          [ 'access_token' => $access_token ]
                      );

        $session->expects($this->once())
                ->method('get_matrix_client')
                ->willReturn($matrix_client);
        $session->expects($this->once())
                ->method('get_user_id')
                ->willReturn($user_id);
        $session->expects($this->once())
                ->method('get_access_token')
                ->willReturn($access_token);

        $admin_session = new Admin_session($session);
        $admin_session->reset_password($user_id, $new_password);
    }

    public function test_deactivate_account(): void {
        $user_id         = '@alice:example.org';
        $access_token    = 'secret-token';
        $matrix_client = $this->createMock(Matrix_client::class);
        $session       = $this->createMock(Session::class);

        $matrix_client->expects($this->once())
                      ->method('post')
                      ->with(
                          SYNAPSE_URL . 'admin/v1/deactivate/' . $user_id,
                          [ 'erase'   => false ],
                          [ 'access_token' => $access_token ]
                      );

        $session->expects($this->once())
                ->method('get_matrix_client')
                ->willReturn($matrix_client);
        $session->expects($this->once())
                ->method('get_user_id')
                ->willReturn($user_id);
        $session->expects($this->once())
                ->method('get_access_token')
                ->willReturn($access_token);

        $admin_session = new Admin_session($session);
        $admin_session->deactivate_account($user_id);
    }
}
