<?php declare(strict_types=1);

include_once "matrix/core/types/ID.php";

use PHPUnit\Framework\TestCase;
use \matrix\Matrix;

final class MatrixTest extends TestCase {
    public function test_make_fqn(): void {
        $server_location = "https://matrix.example.org:8008/";
        $matrix = new Matrix($server_location);
        $fqn = $matrix->make_fqn("vasya");
        $this->assertEquals($fqn, "@vasya:matrix.example.org");
    }
};

?>
