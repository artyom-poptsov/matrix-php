<?php declare(strict_types=1);

include_once "matrix/Room.php";

use \matrix\Room;

use PHPUnit\Framework\TestCase;

final class RoomTest extends TestCase {
    public function test_room_creation(): void {
        $room_alias = '#test:homeserver';
        $room_id    = '!room-id:homeserver';
        $room = new Room($room_alias, $room_id);
        $this->assertEquals($room->get_alias(), $room_alias);
        $this->assertEquals($room->get_id(),    $room_id);
    }
};

?>
