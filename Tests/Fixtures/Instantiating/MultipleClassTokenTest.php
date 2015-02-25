<?hh // strict

use HackPack\HackUnit\Core\TestCase;

class MultipleClassTokenTest extends TestCase
{
    <<test>>
    public function testHasClassToken() : void
    {
        MultipleClassTokenTest::class;
    }
}
