<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\Test;

<<TestSuite>>
class TooManyParams
{
    <<Test>>
    public function test(\HackPack\HackUnit\Assertion\AssertionBuilder $assert, string $required) : void
    {
    }
}
