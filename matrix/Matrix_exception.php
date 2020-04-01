<?php

namespace matrix;

class Matrix_exception extends \Exception {
    private $errcode;
    private $error;
    public function __constructor($message) {
        parent::__construct($message);
    }
    public function __construct($errcode, $error) {
        $this->errcode = $errcode;
        $this->error   = $error;
    }
    public function get_errcode() {
        return $this->errcode;
    }
    public function get_error() {
        return $this->error;
    }
}

?>
