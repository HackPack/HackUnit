<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\Suite;
use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\Interruption;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Test\Runner;
use HackPack\HackUnit\Tests\Doubles\SpySuite;
use HackPack\HackUnit\Tests\Doubles\AsyncSuite;

class RunnerTest {
  private Vector<FailureListener> $failureListeners = Vector {};
  private Vector<SkipListener> $skipListeners = Vector {};
  private Vector<SuccessListener> $successListeners = Vector {};
  private Vector<Assert> $asserts = Vector {};
  private Vector<\Exception> $uncaughtExceptions = Vector {};
  private Runner $runner;
  private int $testsPassed = 0;

  public function __construct() {
    $this->runner = new Runner(inst_meth($this, 'assertionBuilder'));
  }

  public function assertionBuilder(
    Vector<FailureListener> $failures,
    Vector<SkipListener> $skips,
    Vector<SuccessListener> $successes,
  ): Assert {
    $this->failureListeners->addAll($failures);
    $this->skipListeners->addAll($skips);
    $this->successListeners->addAll($successes);

    $assert = new \HackPack\HackUnit\Assert($failures, $skips, $successes);

    $this->asserts->add($assert);
    return $assert;
  }

  <<Test>>
  public function allSuitesAreRun(Assert $assert): void {
    $suites = Vector {};
    for ($i = 0; $i < 3; $i++) {
      $suites->add(new SpySuite());
    }

    $this->runner->run($suites);

    foreach ($suites as $index => $suite) {

      // Tell the typechecker we know what's going on
      invariant($suite instanceof SpySuite, '');

      // Ensure each suite is run once
      $assert->int($suite->counts['up'])->eq(1);
      $assert->int($suite->counts['run'])->eq(1);
      $assert->int($suite->counts['down'])->eq(1);

      // Ensure the suites are passed the generated assert objects
      $assert->mixed($suite->asserts->at(0))
        ->identicalTo($this->asserts->at($index));
    }
  }

  <<Test>>
  public function suitesAreRunAsync(Assert $assert): void {
    // 0.01 second per suite
    $sleepTime = 10000;
    $suites = Vector {};
    for ($i = 0; $i < 3; $i++) {
      $suites->add(new AsyncSuite($sleepTime));
    }

    $start = microtime(true);
    $this->runner->run($suites);
    $end = microtime(true);

    // Convert delta time to microseconds
    $deltaTime = ($end - $start) * 1000000;

    // Total run time should be less than twice the sleep time
    // since all are running in async
    $assert->float($deltaTime)->lt(2.0 * $sleepTime);
  }

  <<Test>>
  public function interruptionEventHandlersAreAdded(Assert $assert): void {
    $this->runner->run(Vector {new SpySuite()});

    $assert->int($this->failureListeners->count())->eq(1);
    $assert->int($this->skipListeners->count())->eq(1);
    $assert->int($this->successListeners->count())->eq(0);

    // Ensure the handlers called throw an Interruption exception
    $assert->whenCalled(
      () ==> {
        $listener = $this->failureListeners->at(0);
        $listener(Failure::fromCallStack('fake failure'));
      },
    )->willThrowClass(Interruption::class);

    $assert->whenCalled(
      () ==> {
        $listener = $this->skipListeners->at(0);
        $listener(Skip::fromCallStack('fake failure'));
      },
    )->willThrowClass(Interruption::class);
  }

  <<Test>>
  public function listenersArePassedToAssertBuilder(Assert $assert): void {
    $failure = ($event) ==> {
    };
    $skip = ($event) ==> {
    };
    $success = ($event) ==> {
    };

    $this->runner->onFailure($failure);
    $this->runner->onSkip($skip);
    $this->runner->onSuccess($success);

    $this->runner->run(Vector {new SpySuite()});

    $assert->int($this->failureListeners->count())->eq(2);
    $assert->int($this->skipListeners->count())->eq(2);
    $assert->int($this->successListeners->count())->eq(1);

    // This also implicitly tests that the interruption handlers are added at the end
    $assert->mixed($this->failureListeners->at(0))->identicalTo($failure);
    $assert->mixed($this->skipListeners->at(0))->identicalTo($skip);
    $assert->mixed($this->successListeners->at(0))->identicalTo($success);
  }

  <<Test>>
  public function testPassListenersAreRun(Assert $assert): void {
    $this->runner->onPass(
      ($e) ==> {
        $this->testsPassed++;
      },
    );

    $suite = new SpySuite();
    $this->runner->run(Vector {$suite});

    $assert->int($suite->passCallbacks->count())->eq(1);
    $passCallback = $suite->passCallbacks->at(0);

    $assert->int($this->testsPassed)->eq(0);
    $passCallback();
    $assert->int($this->testsPassed)->eq(1);
  }

  <<Test>>
  public function unexpectedExceptionIsHandled(Assert $assert): void {
    $this->runner->onUncaughtException(
      $e ==> {
        $this->uncaughtExceptions->add($e);
      },
    );

    $exception = new \Exception('With a message');
    $suite = new SpySuite(
      () ==> {
        throw $exception;
      },
    );

    $assert->whenCalled(
      () ==> {
        $this->runner->run(Vector {$suite});
      },
    )->willNotThrow();

    $assert->container($this->uncaughtExceptions)
      ->containsOnly(Vector {$exception});
    $assert->int($suite->counts['up'])->eq(1);
    $assert->int($suite->counts['run'])->eq(1);
    $assert->int($suite->counts['down'])->eq(1);
  }
}
