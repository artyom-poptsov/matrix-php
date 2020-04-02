<?php

namespace matrix;

require_once("Matrix_exception.php");
require_once("HTTP_client.php");

class Matrix_client extends HTTP_client {

    public function __construct($server_location) {
        parent::__construct($server_location);
    }

    public function handle_result($result) {
        if ($result) {
            $json = json_decode($result, true);
            if (array_key_exists('errcode', $json)) {
                throw new Matrix_exception($json['errcode'], $json['error']);
            }
            return $json;
        } else {
            throw new Matrix_exception("Could not execute request");
        }
    }

    public function post($resource, $data, $params = []) {
        $result = parent::post($resource, $data, $params);
        return $this->handle_result($result);
    }

    public function get($resource, $params = []) {
        $result = parent::get($resource, $params);
        return $this->handle_result($result);
    }
}

?>
