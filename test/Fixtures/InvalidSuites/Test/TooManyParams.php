<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\Test;

class TooManyParams
{
    <<Test>>
    public function test(\HackPack\HackUnit\Assert $assert, string $required) : void
    {
    }
}
