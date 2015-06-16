<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteSetup;

<<TestSuite>>
class Params
{
    <<Setup('suite')>>
    public function setup(string $required) : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
