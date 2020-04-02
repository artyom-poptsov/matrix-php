<?php

namespace matrix;

class HTTP_client {
    private $server;
    private $curl;
    public function __construct($server) {
        $this->server = $server;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct() {
        curl_close($this->curl);
    }

    /**
     * Get the current server.
     *
     * @return HTTP server link as a string.
     */
    public function get_server() {
        return $this->server;
    }

    /**
     * Make an HTTP POST request.
     *
     * @param $resource A resource on the server to use.
     * @param $data A data array to post.
     * @param $params Request parameters (optional.)
     * @return HTTP response from the server.
     */
    public function post($resource, $data, $params = []) {
        if (! empty($params)) {
            $params = '?' . join("&", $params);
        } else {
            $params = '';
        }
        curl_setopt($this->curl, CURLOPT_URL,
                    $this->server . $resource . $params);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($this->curl, CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json'));
        curl_setopt($this->curl, CURLOPT_POST, 1);
        return curl_exec($this->curl);
    }

    /**
     * Make an HTTP GET request.
     *
     * @param $resource A resource on the server to use.
     * @param $params Request parameters (optional.)
     * @return HTTP response from the server.
     */
    public function get($resource, $params = []) {
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        if (! empty($params)) {
            $params = '?' . join("&", $params);
        } else {
            $params = '';
        }
        curl_setopt($this->curl, CURLOPT_URL, $this->server . $resource . $params);
        return curl_exec($this->curl);
    }
}

?>
