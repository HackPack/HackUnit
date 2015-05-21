<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteTeardown;

<<TestSuite>>
class Params
{
    <<TearDown('suite')>>
    public function setup(string $required) : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assertion\AssertionBuilder $assert) : void
    {
    }
}
