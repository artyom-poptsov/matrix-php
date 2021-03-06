<?php

declare(strict_types = 1);

namespace matrix\core;

require_once(dirname(__FILE__) . "/types/ID.php");

use matrix\core\types\ID;

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
     * @param ID $id Room ID.
     */
    public function __construct(ID $alias, ID $id) {
        $this->alias = $alias;
        $this->id    = $id;
    }

    /**
     * Get room alias.
     *
     * @return The room alias.
     */
    public function get_alias() : ID {
        return $this->alias;
    }

    /**
     * Get room ID.
     *
     * @return The room ID.
     */
    public function get_id() : ID {
        return $this->id;
    }
}

?>
