<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteSetup;

<<TestSuite>>
class DestructorTest
{
    <<Setup('suite')>>
    public function __destruct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assertion\AssertionBuilder $assert) : void
    {
    }
}
