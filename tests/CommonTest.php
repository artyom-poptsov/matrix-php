<?php declare(strict_types=1);

include_once "matrix/common.php";

use PHPUnit\Framework\TestCase;

final class CommonTest extends TestCase {
    public function test_FQN_predicate_works(): void {
        $this->assertEquals(\matrix\is_fqn("test"), false);
        $this->assertEquals(\matrix\is_fqn("@alice:example.com"), true);
    }
}
