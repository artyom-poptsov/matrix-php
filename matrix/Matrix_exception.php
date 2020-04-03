<?php

namespace matrix;

class Matrix_exception extends \Exception {
    private $error_code;
    public function __construct($message, $error_code = NULL) {
        parent::__construct($message);
        $this->error_code = $error_code;
    }
    public function get_error_code() {
        return $this->error_code;
    }
}

?>
