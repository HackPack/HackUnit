<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\TestTeardown;

<<TestSuite>>
class Constructor
{
    <<TearDown>>
    public function __construct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
