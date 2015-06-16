<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\TestSetup;

<<TestSuite>>
class Params
{
    <<Setup>>
    public function setup(string $required) : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
