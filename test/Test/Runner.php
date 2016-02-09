<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\Suite;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Test\Runner;
use HackPack\HackUnit\Tests\Doubles\SpySuite;
use HackPack\HackUnit\Tests\Doubles\AsyncSuite;

class RunnerTest
{
    private Vector<FailureListener> $failureListeners = Vector{};
    private Vector<SkipListener> $skipListeners = Vector{};
    private Vector<SuccessListener> $successListeners = Vector{};
    private Vector<Assert> $asserts = Vector{};
    private Runner $runner;

    public function __construct()
    {
        $this->runner = new Runner(inst_meth($this, 'assertionBuilder'));
    }

    public function assertionBuilder(
        Vector<FailureListener> $failures,
        Vector<SkipListener> $skips,
        Vector<SuccessListener> $successes,
    ) : Assert
    {
        $this->failureListeners->addAll($failures);
        $this->skipListeners->addAll($skips);
        $this->successListeners->addAll($successes);

        $assert = new \HackPack\HackUnit\Assert($failures, $skips, $successes);

        $this->asserts->add($assert);
        return $assert;
    }

    <<Test>>
    public function allSuitesAreRun(Assert $assert) : void
    {
        $suites = Vector{};
        for($i = 0; $i < 3; $i++) {
             $suites->add(new SpySuite());
        }

        $this->runner->run($suites);

        foreach($suites as $index => $suite) {

            // Tell the typechecker we know what's going on
            invariant($suite instanceof SpySuite, '');

            // Ensure each suite is run once
            $assert->int($suite->counts['up'])->eq(1);
            $assert->int($suite->counts['run'])->eq(1);
            $assert->int($suite->counts['down'])->eq(1);

            // Ensure the suites are passed the generated assert objects
            $assert->mixed($suite->asserts->at(0))->identicalTo($this->asserts->at($index));
        }
    }

    <<Test>>
    public function suitesAreRunAsync(Assert $assert) : void
    {
        // 0.01 second per suite
        $sleepTime = 10000;
        $suites = Vector{};
        for($i = 0; $i < 3; $i++) {
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
}
