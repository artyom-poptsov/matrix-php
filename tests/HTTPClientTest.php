<?php declare(strict_types=1);

include_once "matrix/core/net/HTTP_client.php";

use \matrix\core\net\HTTP_client;

use PHPUnit\Framework\TestCase;

final class HTTPClientTest extends TestCase {
    public function test_HTTP_client_creation(): void {
        $server_location = 'https://example.org:8008/';
        $client = new HTTP_client($server_location);
        $this->assertEquals($client->get_server(), $server_location);
        $this->assertEquals($client->get_domain(), 'example.org');
        $this->assertEquals($client->get_port(),   '8008');
    }
};

?>
