<?php

namespace matrix;

/**
 * This class describes a Matrix room.
 */
class Room {
    private $alias;
    private $id;

    /**
     * The main constructor.
     *
     * @param $alias Room alias.
     * @param $id    Room ID.
     */
    public function __construct($alias, $id) {
        $this->alias = $alias;
        $this->id    = $id;
    }

    /**
     * Get room alias.
     *
     * @return The room alias.
     */
    public function get_alias() {
        return $this->alias;
    }

    /**
     * Get room ID.
     *
     * @return The room ID.
     */
    public function get_id() {
        return $this->id;
    }
}

?>
