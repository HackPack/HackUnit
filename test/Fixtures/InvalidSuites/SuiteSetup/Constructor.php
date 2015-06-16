<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteSetup;

<<TestSuite>>
class Constructor
{
    <<Setup('suite')>>
    public function __construct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
