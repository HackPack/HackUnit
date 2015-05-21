<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteTeardown;

<<TestSuite>>
class StaticMethods
{
    <<TearDown('suite')>>
    public static function tearItDown() : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assertion\AssertionBuilder $assert) : void
    {
    }
}
