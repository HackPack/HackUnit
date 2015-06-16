<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteTeardown;

<<TestSuite>>
class Destructor
{
    <<TearDown('suite')>>
    public function __destruct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
