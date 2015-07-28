<?hh // strict

namespace HackPack\HackUnit\Tests\Util;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

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

    <<Test>>
    public function functionMustBeString(Assert $assert) : void
    {
        foreach($this->nonStrings() as $mixed) {
            $item = Trace::convert([['function' => $mixed]])->at(0);
            $assert->mixed($item['function'])->isNull();
        }
        foreach(['', '0', '1e10', 'normal string'] as $string) {
            $item = Trace::convert([['function' => $string]])->at(0);
            $assert->mixed($item['function'])->identicalTo($string);
        }
    }

    <<Test>>
    public function classMustBeString(Assert $assert) : void
    {
        foreach($this->nonStrings() as $mixed) {
            $item = Trace::convert([['class' => $mixed]])->at(0);
            $assert->mixed($item['class'])->isNull();
        }
        foreach(['', '0', '1e10', 'normal string'] as $string) {
            $item = Trace::convert([['class' => $string]])->at(0);
            $assert->mixed($item['class'])->identicalTo($string);
        }
    }

    <<Test>>
    public function fileMustBeString(Assert $assert) : void
    {
        foreach($this->nonStrings() as $mixed) {
            $item = Trace::convert([['file' => $mixed]])->at(0);
            $assert->mixed($item['file'])->isNull();
        }
        foreach(['', '0', '1e10', 'normal string'] as $string) {
            $item = Trace::convert([['file' => $string]])->at(0);
            $assert->mixed($item['file'])->identicalTo($string);
        }
    }

    <<Test>>
    public function assertionCallSearchFailsWhenSearchingWithoutAssertion(Assert $assert) : void
    {
        $item = Trace::findAssertionCall();
        $assert->mixed($item['line'])->isNull();
        $assert->mixed($item['function'])->isNull();
        $assert->mixed($item['class'])->isNull();
        $assert->mixed($item['file'])->isNull();
    }

    <<Test>>
    public function assertionCallSearchSucceedsWhenSearchingWithAssertion(Assert $assert) : void
    {
        $mockAssert = new \HackPack\HackUnit\Tests\Mocks\Util\Assertion();
        $items = Vector{};
        $line = __LINE__ + 3;
        $mockAssert->run(() ==> {
            $items->add(Trace::findAssertionCall());
        });

        $assert->int($items->count())->eq(1);
        $item = $items->at(0);

        $assert->mixed($item['line'])->isInt();
        $assert->mixed($item['function'])->isString();
        $assert->mixed($item['class'])->isString();
        $assert->mixed($item['file'])->isString();

        $assert->int((int)$item['line'])->eq($line);
        $assert->string((string)$item['function'])->is(__FUNCTION__);
        $assert->string((string)$item['class'])->is(__CLASS__);
        $assert->string((string)$item['file'])->is(__FILE__);
    }

    private function levelOne() : TraceItem
    {
         return $this->levelTwo();
    }

    private function levelTwo() : TraceItem
    {
        return Trace::findTestMethod();
    }

    <<Test>>
    public function findTestMethod(Assert $assert) : void
    {
        $line = __LINE__ + 1;
        $item = $this->levelOne();

        $assert->mixed($item['line'])->isInt();
        $assert->mixed($item['function'])->isString();
        $assert->mixed($item['class'])->isString();
        $assert->mixed($item['file'])->isString();

        $assert->int((int)$item['line'])->eq($line);
        $assert->string((string)$item['function'])->is(__FUNCTION__);
        $assert->string((string)$item['class'])->is(__CLASS__);
        $assert->string((string)$item['file'])->is(__FILE__);
    }
}
