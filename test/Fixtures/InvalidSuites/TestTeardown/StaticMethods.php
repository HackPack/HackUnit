<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\TestTeardown;

<<TestSuite>>
class StaticMethods
{
    <<TearDown>>
    public static function noStatics() : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
