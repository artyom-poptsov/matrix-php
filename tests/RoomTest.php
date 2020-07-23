<?php declare(strict_types=1);

include_once "matrix/core/types/ID.php";
include_once "matrix/Room.php";

use \matrix\core\types\ID;
use \matrix\Room;

use PHPUnit\Framework\TestCase;

final class RoomTest extends TestCase {
    public function test_room_creation(): void {
        $room_alias = '#test:homeserver';
        $room_id    = '!room-id:homeserver';
        $room = new Room(new ID($room_alias), new ID($room_id));
        $this->assertEquals($room->get_alias()->to_string(), $room_alias);
        $this->assertEquals($room->get_id()->to_string(),    $room_id);
    }
};

?>
