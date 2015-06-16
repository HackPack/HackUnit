<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteSetup;

<<TestSuite>>
class Destructor
{
    <<Setup('suite')>>
    public function __destruct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
