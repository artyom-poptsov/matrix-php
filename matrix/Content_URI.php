<?php

declare(strict_types = 1);

namespace matrix;

/**
 * This class describes Matrix media URI.
 */
class Content_URI {
    /**
     * Content URI in the format
     *     mxc://<server-name>/<media-id>
     */
    private string $uri;

    public function __construct($uri) {
        $this->uri = $uri;
    }

    /**
     * Get the server part of the URI.
     * @return ?string A server name.
     */
    public function get_server_name() : ?string {
        $result = preg_match("/mxc:\/\/([^\/]+)\/*/", $this->uri,
                             $matches);
        return ($result > 0) ? $matches[1] : NULL;
    }

    /**
     * Get the media ID part of the URI.
     *
     * @return ?string A media ID.
     */
    public function get_media_id() : ?string {
        $result = preg_match("/mxc:\/\/[^\/]+\/([^\/]+)/", $this->uri,
                             $matches);
        return ($result > 0) ? $matches[1] : NULL;
    }

    /**
     * Get the protocol from the URI. With a valid URI, should be always "MXC".
     *
     * @return ?string A protocol string.
     */
    public function get_protocol() : ?string {
        $result = preg_match('/([a-z]+):\/\/*/', $this->uri,
                             $matches);
        return ($result > 0) ? $matches[1] : NULL;
    }

    /**
     * Check if the URI is valid.
     *
     * @return bool true if it is valid, false otherwise.
     */
    public function is_valid() : bool {
        $server_name = $this->get_server_name();
        $media_id    = $this->get_media_id();
        $protocol    = $this->get_protocol();
        return ($server_name != NULL)
                              && ($media_id != NULL)
                              && ($protocol != NULL)
                              && ($protocol === 'mxc');
    }
}

?>
