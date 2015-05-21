<?hh // strict

namespace HackPack\HackUnit\Tests\Util;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Util\Trace;

<<TestSuite>>
class TraceTest
{
    <<Test>>
    public function emptyItemReturnsNull(AssertionBuilder $assert) : void
    {
        $item = Trace::buildItem([]);
        $assert->context($item['line'])->isNull();
        $assert->context($item['function'])->isNull();
        $assert->context($item['class'])->isNull();
        $assert->context($item['file'])->isNull();
    }

    <<Test>>
    public function ensureLineMustBeInt(AssertionBuilder $assert) : void
    {
        $assert->context(Trace::buildItem(['line' => true])['line'])->isNull();
        $assert->context(Trace::buildItem(['line' => 'a'])['line'])->isNull();
        $assert->context(Trace::buildItem(['line' => 0.1])['line'])->isNull();
        $assert->context(Trace::buildItem(['line' => Map{}])['line'])->isNull();
        $assert->context(Trace::buildItem(['line' => 2])['line'])->identicalTo(2);
        $assert->context(Trace::buildItem(['line' => -2])['line'])->identicalTo(-2);
    }
}
