<?hh // strict

namespace HackPack\HackUnit\Tests\Util;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\Trace;

<<TestSuite>>
class TraceTest
{
    private function nonStrings() : Vector<mixed>
    {
        return Vector{
            1,
            0,
            -1,
            true,
            false,
            0.1,
            0.0,
            -1.1,
            [],
            ['a'],
            new TraceTest(),
        };
    }

    private function nonInts() : Vector<mixed>
    {
        return Vector{
            'a',
            'longer string?',
            true,
            false,
            0.1,
            0.0,
            -1.1,
            [],
            ['a'],
            new TraceTest(),
        };
    }

    <<Test>>
    public function missingParamsResultsInNull(Assert $assert) : void
    {
        $raw = [];
        $items = Trace::convert([$raw]);
        $assert->int($items->count())->eq(1);

        $item = $items->at(0);

        $assert->mixed($item['line'])->isNull();
        $assert->mixed($item['function'])->isNull();
        $assert->mixed($item['class'])->isNull();
        $assert->mixed($item['file'])->isNull();
    }

    <<Test>>
    public function lineMustBeInteger(Assert $assert) : void
    {
        foreach($this->nonInts() as $mixed) {
            $item = Trace::convert([['line' => $mixed]])->at(0);
            $assert->mixed($item['line'])->isNull();
        }
        foreach([-1, 0, 1, 3] as $int) {
            $item = Trace::convert([['line' => $int]])->at(0);
            $assert->mixed($item['line'])->identicalTo($int);
        }
    }
}
