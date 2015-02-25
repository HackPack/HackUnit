<?hh //strict

use HackPack\HackUnit\Core\TestCase;

class ThreeTest extends TestCase
{
    <<test>>
    public function testFive(): void
    {
    }

    <<test>>
    public function testSix(): void
    {
    }

    <<groupLoad>>
    public function runMeOnce(): void
    {
    }

    <<groupUnload>>
    public function rumeMeOnceToo(): void
    {
    }
}
