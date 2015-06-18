<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\Util;

class Assertion implements \HackPack\HackUnit\Contract\Assertion\Assertion
{
    public function run((function():void) $f) : void
    {
        $f();
    }
}
