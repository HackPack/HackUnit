<?hh //strict

use HackPack\HackUnit\Core\TestCase;

class TwoTest extends TestCase
{
    <<test>>
    public function testThree(): void
    {
    }

    <<test>>
    public function testFour(): void
    {
    }

    <<tearDown>>
    public function tearDownTwo(): void
    {
    }
}
