<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\Test;

use HackPack\HackUnit\Contract\Assert;

<<TestSuite>>
class TooFewParams
{
    <<Test>>
    public function test() : void
    {
    }

    <<Test>>
    public function goodTest(Assert $assert) : void
    {
    }
}
