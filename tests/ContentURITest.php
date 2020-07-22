<?php declare(strict_types=1);

include_once "matrix/core/types/Content_URI.php";

use \matrix\core\types\Content_URI;

use PHPUnit\Framework\TestCase;

final class ContentURITest extends TestCase {
    public function test_to_string(): void {
        $content_uri = new Content_URI('mxc://example.org/OBdyNuZznTixmWfpsffMCfhO');
        $this->assertEquals($content_uri->to_string(),
                            'mxc://example.org/OBdyNuZznTixmWfpsffMCfhO');
    }

    public function test_get_server_name(): void {
        $content_uri = new Content_URI('mxc://example.org/OBdyNuZznTixmWfpsffMCfhO');
        $this->assertEquals($content_uri->get_server_name(),
                            'example.org');
    }

    public function test_get_media_id(): void {
        $content_uri = new Content_URI('mxc://example.org/OBdyNuZznTixmWfpsffMCfhO');
        $this->assertEquals($content_uri->get_media_id(),
                            'OBdyNuZznTixmWfpsffMCfhO');
    }

    public function test_get_protocol(): void {
        $content_uri = new Content_URI('mxc://example.org/OBdyNuZznTixmWfpsffMCfhO');
        $this->assertEquals($content_uri->get_protocol(),
                            'mxc');
    }

    public function test_is_valid_true(): void {
        $content_uri = new Content_URI('mxc://example.org/OBdyNuZznTixmWfpsffMCfhO');
        $this->assertEquals($content_uri->is_valid(), true);
    }

    public function test_is_valid_false(): void {
        $content_uri = new Content_URI('http://example.org/OBdyNuZznTixmWfpsffMCfhO');
        $this->assertEquals($content_uri->is_valid(), false);
    }
};
