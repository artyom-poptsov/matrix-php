<?php

declare(strict_types = 1);

namespace matrix\core\types;

/**
 * This class describes a Matrix ID type.
 */
class ID_type {
    const USER  = "user";
    const ROOM  = "room";
    const ALIAS = "alias";
    const EVENT = "event";
}

const TYPE_MAPPING = [
    '@' => ID_type::USER,
    '!' => ID_type::ROOM,
    '#' => ID_type::ALIAS,
    '$' => ID_type::EVENT
];

/**
 * This class describes a Matrix ID.
 */
class ID {
    /**
     * A Matrix ID.
     */
    private string $id;

    public function __construct(string $id) {
        $this->id = $id;
    }

    /**
     * Return the string representation of the Matrix ID.
     *
     * @return string A Matrix ID.
     */
    public function to_string() : string {
        return $this->id;
    }

    /**
     * Get the type of the Matrix ID.
     *
     * @return ?string Type of the Matrix ID, or NULL if the type is unknown.
     */
    public function get_type() : ?string {
        return array_key_exists($this->id[0], TYPE_MAPPING)
            ? TYPE_MAPPING[$this->id[0]] : NULL;
    }

    /**
     * Get the identity part from the ID.
     *
     * @return ?string An identity or NULL.
     */
    public function get_id() : ?string {
        $result = preg_match("/.?([^:]+):.*/",
                             $this->id,
                             $matches);
        return ($result > 0) ? $matches[1] : NULL;
    }

    /**
     * Get the server part from the ID.
     *
     * @return ?string The server name or NULL.
     */
    public function get_server() : ?string {
        $result = preg_match("/.?[^:]+:(.*)/",
                             $this->id,
                             $matches);
        return ($result > 0) ? $matches[1] : NULL;
    }

    /**
     * Predicate.  Check if the Matrix ID is correct.
     *
     * @return bool true if the ID is correct, false otherwise.
     */
    public function is_valid() : bool {
        $type   = $this->get_type();
        $id     = $this->get_id();
        $server = $this->get_server();
        return ($type != NULL)
                       && ($id != NULL)
                       && ($server != NULL);
    }
}

?>
