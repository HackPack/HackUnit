<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteSetup;

<<TestSuite>>
class StaticMethods
{
    <<Setup('suite')>>
    public static function setItUp() : void
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
