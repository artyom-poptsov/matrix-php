<?php declare(strict_types=1);

include_once "matrix/core/types/ID.php";

use PHPUnit\Framework\TestCase;
use \matrix\core\types\ID;
use \matrix\core\types\ID_type;

final class IDTest extends TestCase {
    public function test_constructor(): void {
        $id_string = '#test:homeserver';
        $id = new ID($id_string);
        $this->assertEquals($id->to_string(), $id_string);
    }

    public function test_get_type(): void {
        $id_alias = new ID('#test:homeserver');
        $id_user  = new ID('@test:homeserver');
        $id_room  = new ID('!test:homeserver');
        $id_event = new ID('$test:homeserver');
        $this->assertEquals($id_alias->get_type(), ID_type::ALIAS);
        $this->assertEquals($id_user->get_type(),  ID_type::USER);
        $this->assertEquals($id_room->get_type(),  ID_type::ROOM);
        $this->assertEquals($id_event->get_type(), ID_type::EVENT);
    }

    public function test_get_id(): void {
        $id_string = '#test:homeserver';
        $id = new ID($id_string);
        $this->assertEquals($id->get_id(), 'test');
    }

    public function test_get_server(): void {
        $id_string = '#test:homeserver';
        $id = new ID($id_string);
        $this->assertEquals($id->get_server(), 'homeserver');
    }

    public function test_is_valid(): void {
        $valid_id_1 = new ID('#test:homeserver');
        $invalid_id_1 = new ID('wrong:id');
        $invalid_id_2 = new ID('@wrong');
        $this->assertTrue($valid_id_1->is_valid());
        $this->assertFalse($invalid_id_1->is_valid());
        $this->assertFalse($invalid_id_2->is_valid());
    }
};

?>
