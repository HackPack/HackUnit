<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteTeardown;

<<TestSuite>>
class Constructor
{
    <<TearDown('suite')>>
    public function __construct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert) : void
    {
    }
}
