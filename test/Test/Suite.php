<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\Interruption;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Test\Suite;
use HackPack\HackUnit\Test\Test as TestShape;
use HackPack\HackUnit\Test\InvokerWithParams;
use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;
use HH\Asio;

class SuiteTest {
  private int $factoryRuns = 0;
  private int $suiteUpRuns = 0;
  private int $suiteDownRuns = 0;
  private int $testUpRuns = 0;
  private int $testDownRuns = 0;
  private int $testRuns = 0;
  private int $passedEvents = 0;
  private Vector<Skip> $skipEvents = Vector {};

  private (function(): Awaitable<mixed>) $factory;
  private TraceItem $traceItem;

  public function __construct() {
    $this->factory = async () ==> {
      $this->factoryRuns++;
      return $this;
    };
    $this->traceItem = Trace::buildItem([]);
  }

  <<Test>>
  public function suiteTests(Assert $assert): void {
    foreach (range(0, 3) as $thirdTestCount) {

      $tests = Vector {};
      for ($i = 0; $i < $thirdTestCount; $i++) {
        $tests->add($this->makeInterruptedTest($assert));
        $tests->add($this->makeSkippedTest($assert));
        $tests->add($this->makePassingTest($assert));
      }
      $this->runTests($assert, $tests);
    }
  }

  private function runTests(Assert $assert, Vector<TestShape> $tests): void {
    $thirdTestCount = (int) floor($tests->count() / 3);

    foreach (range(0, 2) as $upDownCount) {
      $suite = new Suite(
        $tests,
        $this->repeat($upDownCount, $this->makeSuiteUp($assert)),
        $this->repeat($upDownCount, $this->makeSuiteDown($assert)),
        $this->repeat($upDownCount, $this->makeTestUp($assert)),
        $this->repeat($upDownCount, $this->makeTestDown($assert)),
      );

      $this->runSuite($suite);

      // Skipped tests shouldn't run the factory
      $assert->int($this->factoryRuns)->eq(2 * $thirdTestCount);

      // Skipped tests shouldn't be run
      $assert->int($this->testRuns)->eq(2 * $thirdTestCount);

      // Interrupted and skipped tests shouldn't be passed
      $assert->int($this->passedEvents)->eq($thirdTestCount);

      // Make sure the skip event is triggered for skipped tests
      $assert->int($this->skipEvents->count())->eq($thirdTestCount);

      // Make sure the test trace item is passed to the skip event
      if ($thirdTestCount > 0) {
        $event = $this->skipEvents->at(0);
        $assert->mixed($event->callSite())->identicalTo($this->traceItem);
      }

      // Test up/down should not run skipped tests, should run for interrupted tests
      $assert->int($this->testUpRuns)->eq(2 * $thirdTestCount * $upDownCount);
      $assert->int($this->testDownRuns)
        ->eq(2 * $thirdTestCount * $upDownCount);

      // Running shouldn't trigger suite up/down
      $assert->int($this->suiteUpRuns)->eq(0);
      $assert->int($this->suiteDownRuns)->eq(0);

      // Should be independent of test count, and only ups are run
      Asio\join($suite->up());
      $assert->int($this->suiteUpRuns)->eq($upDownCount);
      $assert->int($this->suiteDownRuns)->eq(0);

      // Should be independent of test count, and only downs are run
      Asio\join($suite->down());
      $assert->int($this->suiteUpRuns)->eq($upDownCount);
      $assert->int($this->suiteDownRuns)->eq($upDownCount);

      $this->resetCounts();
    }
  }

  private function resetCounts(): void {
    $this->factoryRuns = 0;
    $this->suiteUpRuns = 0;
    $this->suiteDownRuns = 0;
    $this->testUpRuns = 0;
    $this->testDownRuns = 0;
    $this->testRuns = 0;
    $this->passedEvents = 0;
    $this->skipEvents->clear();
  }

  private function repeat<T>(int $count, T $item): Vector<T> {
    $list = Vector {};
    $list->resize($count, $item);
    return $list;
  }

  private function makeTestMethod(Assert $assert): InvokerWithParams {
    return async ($instance, $args) ==> {
      $assert->mixed($instance)->identicalTo($this);
      $assert->int(count($args))->eq(1);
      $assert->mixed($args[0])->isTypeOf(Assert::class);
      $this->testRuns++;
    };
  }

  private function makePassingTest(Assert $assert): TestShape {
    return shape(
      'factory' => $this->factory,
      'method' => $this->makeTestMethod($assert),
      'trace item' => $this->traceItem,
      'skip' => false,
    );
  }

  private function makeSkippedTest(Assert $assert): TestShape {
    return shape(
      'factory' => $this->factory,
      'method' => $this->makeTestMethod($assert),
      'trace item' => $this->traceItem,
      'skip' => true,
    );
  }

  private function makeInterruptedTest(Assert $assert): TestShape {
    return shape(
      'factory' => $this->factory,
      'method' => async ($instance, $args) ==> {
        $this->testRuns++;
        throw new Interruption();
      },
      'trace item' => $this->traceItem,
      'skip' => false,
    );
  }

  private function makeSuiteUp(Assert $assert): InvokerWithParams {
    return async ($instance, $params) ==> {
      $this->suiteUpRuns++;
      // All suite up methods are static, so no instance should be passed
      $assert->mixed($instance)->isNull();
    };
  }

  private function makeSuiteDown(Assert $assert): InvokerWithParams {
    return async ($instance, $params) ==> {
      $this->suiteDownRuns++;
      // All suite down methods are static, so no instance should be passed
      $assert->mixed($instance)->isNull();
    };
  }

  private function makeTestUp(Assert $assert): InvokerWithParams {
    return
      async ($instance, $params) ==> {
        $this->testUpRuns++;
        // The test factories all return $this.  Ensure it is passed to the test up methods.
        $assert->mixed($instance)->identicalTo($this);
      };
  }

  private function makeTestDown(Assert $assert): InvokerWithParams {
    return
      async ($instance, $params) ==> {
        $this->testDownRuns++;
        // The test factories all return $this.  Ensure it is passed to the test up methods.
        $assert->mixed($instance)->identicalTo($this);
      };
  }

  private function runSuite(Suite $suite): void {
    Asio\join(
      $suite->run(
        $this->makeAssert(),
        () ==> {
          $this->passedEvents++;
        },
      ),
    );
  }

  private function makeAssert(): Assert {
    $skipListener = ($skipEvent) ==> {
      $this->skipEvents->add($skipEvent);
    };

    return new \HackPack\HackUnit\Assert(
      Vector {},
      Vector {$skipListener},
      Vector {},
    );
  }
}
