<?php

namespace matrix;

class HTTP_client {
    private $server;
    private $curl;

    /**
     * The main class constructor.
     *
     * @param $server A Matrix server URL.
     * @param $is_debug_mode_enabled Sets whether Curl verbose mode will be
     *    enabled or not.  Defaults to false.
     */
    public function __construct($server, $is_debug_mode_enabled = false) {
        $this->server = $server;
        $this->curl = curl_init();
        $this->set_debug_mode($is_debug_mode_enabled);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct() {
        curl_close($this->curl);
    }

    /**
     * Set Curl debug mode to $value.
     *
     * @param $is_enabled Is debug mode enabled?
     */
    public function set_debug_mode($is_enabled) {
        curl_setopt($this->curl, CURLOPT_VERBOSE, $is_enabled);
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
            $params = '?' . join("&", array_map(function ($key, $value){
                return $key . '=' . $value;
            }, array_keys($params), $params));
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
        curl_setopt($this->curl, CURLOPT_POST, 0);
        if (! empty($params)) {
            $params = '?' . join("&", array_map(function ($key, $value){
                return $key . '=' . $value;
            }, array_keys($params), $params));
        } else {
            $params = '';
        }
        curl_setopt($this->curl, CURLOPT_URL, $this->server . $resource . $params);
        return curl_exec($this->curl);
    }
}

?>
