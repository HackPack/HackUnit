<?hh //strict

use HackPack\HackUnit\Core\TestCase;

class OneTest extends TestCase
{
    <<test>>
    public function testOne(): void
    {
    }

    <<test>>
    public function testTwo(): void
    {
    }

    public function notAConventionalTest(): void
    {
    }

    <<test>>
    private function notAPublicTest(): void
    {
    }

    <<setUp>>
    public function setUpOne(): void
    {
    }
}
