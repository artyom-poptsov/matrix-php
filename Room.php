<?php

namespace matrix;

class Room {
    private $alias;
    private $id;

    public function __construct($alias, $id) {
        $this->alias = $alias;
        $this->id    = $id;
    }

    public function get_alias() {
        return $this->alias;
    }
    public function get_id() {
        return $this->id;
    }
}

?>
