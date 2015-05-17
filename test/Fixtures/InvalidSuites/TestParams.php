<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

<<TestSuite>>
class TestParams
{
    <<Test>>
    public function testit() : void
    {
    }

    <<Test>>
    public function testitAgain(string $notAsserBuilder) : void
    {
    }
}
