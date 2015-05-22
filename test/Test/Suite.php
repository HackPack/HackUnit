<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\Skip;

<<TestSuite>>
class Suite
{
    private Vector<Skip> $skipEvents = Vector{};

    <<Setup>>
    public function resetSkipCollection() : void
    {
        $this->skipEvents->clear();
    }

    <<Test>>
    public function suiteCanSkip(AssertionBuilder $assert) : void
    {
        // Suite skip method is called by tests marked skip
        // So the definition line is reported
        $defineLine = __LINE__ - 4;
        $suite = new \HackPack\HackUnit\Test\Suite('filename', 'classname', false);
        $suite->registerSkipHandlers([
            $e ==> {$this->skipEvents->add($e);}
        ]);

        $suite->skip(new \ReflectionMethod(__CLASS__, __FUNCTION__));
        $assert->context($this->skipEvents->count())->identicalTo(1);
        $event = $this->skipEvents->at(0);
        $assert->context($event->assertionLine())->identicalTo($defineLine);
        $assert->context($event->testMethod())->identicalTo(__FUNCTION__);
        $assert->context($event->testClass())->identicalTo(__CLASS__);
        $assert->context($event->testFile())->identicalTo(__FILE__);
    }
}
