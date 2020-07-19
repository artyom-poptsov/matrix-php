<?php

declare(strict_types = 1);

namespace matrix;

/**
 * A HTTP client based on CURL.
 */
class HTTP_client {
    /**
     * Server URL.
     */
    private string $server;

    /**
     * A CURL instance.
     */
    private $curl;

    /**
     * The main class constructor.
     *
     * @param string  $server A Matrix server URL.
     * @param boolean $is_debug_mode_enabled Sets whether Curl verbose mode will
     *    be enabled or not. Defaults to false.
     */
    public function __construct(string $server, bool $is_debug_mode_enabled = false) {
        $this->server = $server;
        $this->curl = curl_init();
        $this->set_debug_mode($is_debug_mode_enabled);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * The main class destructor that closes the current CURL session.
     */
    public function __destruct() {
        curl_close($this->curl);
    }

    /**
     * Get Curl handle.
     *
     * @return mixed Curl handle.
     */
    protected function get_curl() {
        return $this->curl;
    }

    /**
     * Set Curl option.
     *
     * @param int $option Option to set.
     * @param $value Value to set.
     * @return void
     */
    protected function set_opt(int $option, $value): void {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * Set Curl debug mode to $value.
     *
     * @param boolean $is_enabled Is debug mode enabled?
     */
    public function set_debug_mode(bool $is_enabled): void {
        $this->set_opt(CURLOPT_VERBOSE, $is_enabled);
    }

    /**
     * Get the current server.
     *
     * @return HTTP server link as a string.
     */
    public function get_server(): string {
        return $this->server;
    }

    /**
     * Get domain name from the server URL.
     *
     * @return Domain name as a string.
     */
    public function get_domain(): string {
        return parse_url($this->server)['host'];
    }

    /**
     * Get server port from the URL.
     *
     * @return Port number;
     */
    public function get_port(): int {
        return parse_url($this->server)['port'];
    }

    /**
     * Make an HTTP POST request.
     *
     * @param string $resource A resource on the server to use.
     * @param        $data A data to post.
     * @param array  $params Request parameters (optional.)
     * @param array  $headers Request headers (optional.)
     * @return HTTP response from the server.
     */
    public function post(string $resource, $data,
                         array $params  = [],
                         array $headers = []) {
        if (! empty($params)) {
            $params = '?' . join("&", array_map(function ($key, $value){
                return $key . '=' . $value;
            }, array_keys($params), $params));
        } else {
            $params = '';
        }
        $this->set_opt(CURLOPT_CUSTOMREQUEST, "POST");
        $this->set_opt(CURLOPT_URL,
                    $this->server . $resource . $params);
        $this->set_opt(CURLOPT_POSTFIELDS, $data);
        $this->set_opt(CURLOPT_HTTPHEADER, $headers);
        $this->set_opt($this->curl, CURLOPT_POST, 1);
        return curl_exec($this->curl);
    }

    /**
     * Post a file to a server.
     *
     * @param string $resource A resource on the server to use.
     * @param string $file_path A full path to a file.
     * @param array  $params Request parameters (optional.)
     * @param array  $headers Request headers (optional.)
     */
    public function post_file(string $resource,
                              string $file_path,
                              array $params = [],
                              array $headers = []) {
        if (! empty($params)) {
            $params = '?' . join("&", array_map(function ($key, $value){
                return $key . '=' . $value;
            }, array_keys($params), $params));
        } else {
            $params = '';
        }
        $file = NULL;
        if (function_exists('curl_file_create')) {
            $file = curl_file_create($file_path);
        } else {
            $file = '@' . realpath($file_path);
        }
        $params['file'] = $file;

        $this->set_opt(CURLOPT_CUSTOMREQUEST, "POST");
        $this->set_opt(CURLOPT_URL,
                       $this->server . $resource . $params);
        $this->set_opt(CURLOPT_POSTFIELDS, $data);
        $this->set_opt(CURLOPT_HTTPHEADER, $headers);
        $this->set_opt($this->curl, CURLOPT_POST, 1);
        return curl_exec($this->curl);
    }

    /**
     * Make an HTTP GET request.
     *
     * @param string $resource A resource on the server to use.
     * @param array  $params Request parameters (optional.)
     * @return HTTP response from the server.
     */
    public function get(string $resource, array $params = []) {
        $this->set_opt(CURLOPT_HEADER, 0);
        $this->set_opt(CURLOPT_POST, 0);
        $this->set_opt(CURLOPT_CUSTOMREQUEST, "GET");
        if (! empty($params)) {
            $params = '?' . join("&", array_map(function ($key, $value){
                return $key . '=' . $value;
            }, array_keys($params), $params));
        } else {
            $params = '';
        }
        $this->set_opt(CURLOPT_URL, $this->server . $resource . $params);
        return curl_exec($this->curl);
    }

    /**
     * Make an HTTP PUT request.
     *
     * @param string $resource A resource on the server to use.
     * @param        $data A data array to put.
     * @param array  $params Request parameters (optional.)
     * @param array  $headers Request headers (optional.)
     * @return HTTP response from the server.
     */
    public function put(string $resource, $data,
                        array $params  = [],
                        array $headers = []) {
        $this->set_opt(CURLOPT_HEADER, 0);
        $this->set_opt(CURLOPT_CUSTOMREQUEST, "PUT");
        if (! empty($params)) {
            $params = '?' . join("&", array_map(function ($key, $value){
                return $key . '=' . $value;
            }, array_keys($params), $params));
        } else {
            $params = '';
        }

        $this->set_opt(CURLOPT_URL, $this->server . $resource . $params);
        $this->set_opt(CURLOPT_POSTFIELDS, $data);
        $this->set_opt(CURLOPT_HTTPHEADER, $headers);
        return curl_exec($this->curl);
    }
}

?>
