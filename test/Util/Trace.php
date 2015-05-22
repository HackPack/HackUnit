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
        $assert->context(Trace::buildItem(['line' => null])['line'])->isNull();
    }

    <<Test>>
    public function ensureFunctionMustMeString(AssertionBuilder $assert) : void
    {
        $assert->context(Trace::buildItem(['function' => true])['function'])->isNull();
        $assert->context(Trace::buildItem(['function' => 'a'])['function'])->identicalTo('a');
        $assert->context(Trace::buildItem(['function' => 0.1])['function'])->isNull();
        $assert->context(Trace::buildItem(['function' => Map{}])['function'])->isNull();
        $assert->context(Trace::buildItem(['function' => 2])['function'])->isNull();
        $assert->context(Trace::buildItem(['function' => -2])['function'])->isNull();
        $assert->context(Trace::buildItem(['function' => null])['function'])->isNull();
    }

    <<Test>>
    public function ensureClassMustMeString(AssertionBuilder $assert) : void
    {
        $assert->context(Trace::buildItem(['class' => true])['class'])->isNull();
        $assert->context(Trace::buildItem(['class' => 'a'])['class'])->identicalTo('a');
        $assert->context(Trace::buildItem(['class' => 0.1])['class'])->isNull();
        $assert->context(Trace::buildItem(['class' => Map{}])['class'])->isNull();
        $assert->context(Trace::buildItem(['class' => 2])['class'])->isNull();
        $assert->context(Trace::buildItem(['class' => -2])['class'])->isNull();
        $assert->context(Trace::buildItem(['class' => null])['class'])->isNull();
    }

    <<Test>>
    public function ensureFileMustMeString(AssertionBuilder $assert) : void
    {
        $assert->context(Trace::buildItem(['file' => true])['file'])->isNull();
        $assert->context(Trace::buildItem(['file' => 'a'])['file'])->identicalTo('a');
        $assert->context(Trace::buildItem(['file' => 0.1])['file'])->isNull();
        $assert->context(Trace::buildItem(['file' => Map{}])['file'])->isNull();
        $assert->context(Trace::buildItem(['file' => 2])['file'])->isNull();
        $assert->context(Trace::buildItem(['file' => -2])['file'])->isNull();
        $assert->context(Trace::buildItem(['file' => null])['file'])->isNull();
    }

    <<Test>>
    public function traceCanFindAssertCall(AssertionBuilder $assert) : void
    {
        $ref = Vector{};
        $assert->whenCalled(() ==> {
            $ref->add(Trace::findAssertionCall());
        })->willNot()->raiseException();
        $traceItem = $ref->at(0);
        // Trace shows line of the ending paren
        $assert->context($traceItem['line'])->identicalTo(__LINE__ - 3);
        $assert->context($traceItem['function'])->identicalTo(__FUNCTION__);
        $assert->context($traceItem['class'])->identicalTo(__CLASS__);
        $assert->context($traceItem['file'])->identicalTo(__FILE__);
    }

    <<Test>>
    public function traceStartsInThisMethod(AssertionBuilder $assert) : void
    {
        $stack = Trace::generate();
        $assert->context($stack->count())->greaterThan(1);
        $traceItem = $stack->at(0);
        $assert->context($traceItem['line'])->identicalTo(null);
        $assert->context($traceItem['function'])->identicalTo(__FUNCTION__);
        $assert->context($traceItem['class'])->identicalTo(__CLASS__);
        $assert->context($traceItem['file'])->identicalTo(null);
    }
}
