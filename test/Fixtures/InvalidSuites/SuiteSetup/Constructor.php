<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\SuiteSetup;

class Constructor
{
    <<Setup('suite')>>
    public function __construct()
    {
    }

    <<Test>>
    public function test(\HackPack\HackUnit\Contract\Assert $assert) : void
    {
    }
}
