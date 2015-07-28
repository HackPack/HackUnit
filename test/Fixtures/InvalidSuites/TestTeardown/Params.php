<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\TestTeardown;

<<TestSuite>>
class Params
{
    <<TearDown>>
    public function params(string $required) : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
