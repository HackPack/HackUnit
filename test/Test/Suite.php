<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Test\Suite;
use HackPack\HackUnit\Test\Test as TestShape;
use HackPack\HackUnit\Test\InvokerWithParams;
use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;
use HH\Asio;

class SuiteTest
{
    private int $factoryRuns = 0;
    private int $suiteUpRuns = 0;
    private int $suiteDownRuns = 0;
    private int $testUpRuns = 0;
    private int $testDownRuns = 0;
    private int $testRuns = 0;
    private int $passedEvents = 0;
    private Vector<Skip> $skipEvents = Vector{};

    private (function():Awaitable<mixed>) $factory;
    private InvokerWithParams $testMethod;
    private TraceItem $traceItem;

    public function __construct()
    {
        $this->factory = async () ==> {$this->factoryRuns++; return $this;};
        $this->testMethod = async ($instance, $args) ==> {$this->testRuns++;};
        $this->traceItem = Trace::buildItem([]);
    }

    <<Test>>
    public function allTestsAreRun(Assert $assert) : void
    {
        $suite = new Suite(
            Vector{
                $this->makePassingTest(),
                $this->makePassingTest(),
            },
            Vector{},
            Vector{},
            Vector{},
            Vector{},
        );

        $this->runSuite($suite);

        // Make sure tests marked skip aren't actually run
        $assert->int($this->factoryRuns)->eq(2);
        $assert->int($this->testRuns)->eq(2);
    }

    <<Test>>
    public function someTestsAreSkipped(Assert $assert) : void
    {
        $suite = new Suite(
            Vector{
                $this->makeSkippedTest(),
                $this->makePassingTest(),
            },
            Vector{},
            Vector{},
            Vector{},
            Vector{},
        );

        $this->runSuite($suite);

        // Skipped tests do not need to run the factory or the test
        $assert->int($this->factoryRuns)->eq(1);
        $assert->int($this->testRuns)->eq(1);

        $assert->int($this->skipEvents->count())->eq(1);
        $event = $this->skipEvents->at(0);
        $assert->mixed($event->callSite())->identicalTo($this->traceItem);
    }

    private function makePassingTest() : TestShape
    {
        return shape(
            'factory' => $this->factory,
            'method' => $this->testMethod,
            'trace item' => $this->traceItem,
            'skip' => false,
        );
    }

    private function makeSkippedTest() : TestShape
    {
        return shape(
            'factory' => $this->factory,
            'method' => $this->testMethod,
            'trace item' => $this->traceItem,
            'skip' => true,
        );
    }

    private function makeSuiteUp(Assert $assert) : InvokerWithParams
    {
        return async ($instance, $params) ==> {
            $this->suiteUpRuns++;
            // All suite up methods are static, so no instance should be passed
            $assert->mixed($instance)->isNull();
        };
    }

    private function makeSuiteDown(Assert $assert) : InvokerWithParams
    {
        return async ($instance, $params) ==> {
            $this->suiteDownRuns++;
            // All suite down methods are static, so no instance should be passed
            $assert->mixed($instance)->isNull();
        };
    }

    private function makeTestUp(Assert $assert) : InvokerWithParams
    {
        return async ($instance, $params) ==> {
            $this->testUpRuns++;
            // The test factories all return $this.  Ensure it is passed to the test up methods.
            $assert->mixed($instance)->identicalTo($this);
        };
    }

    private function makeTestDown(Assert $assert) : InvokerWithParams
    {
        return async ($instance, $params) ==> {
            $this->testDownRuns++;
            // The test factories all return $this.  Ensure it is passed to the test up methods.
            $assert->mixed($instance)->identicalTo($this);
        };
    }

    private function runSuite(Suite $suite) : void
    {
        Asio\join(
            $suite->run(
                $this->makeAssert(),
                () ==> {$this->passedEvents++;},
            )
        );
    }

    private function makeAssert() : Assert
    {
        $skipListener = ($skipEvent) ==> {
            $this->skipEvents->add($skipEvent);
        };

        return new \HackPack\HackUnit\Assert(
            Vector{},
            Vector{$skipListener},
            Vector{},
        );
    }
}
