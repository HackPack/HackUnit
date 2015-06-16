<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\TestCase;
use HackPack\HackUnit\Contract\Test\Suite;
use HackPack\HackUnit\Event\ExceptionListener;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\Interruption;
use HackPack\HackUnit\Event\PassListener;
use HackPack\HackUnit\Event\RunEndListener;
use HackPack\HackUnit\Event\RunStartListener;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Event\SuiteEndListener;
use HackPack\HackUnit\Event\SuiteStartListener;

class Runner implements \HackPack\HackUnit\Contract\Util\Runner
{
    private Vector<ExceptionListener> $exceptionListeners = Vector{};
    private Vector<FailureListener> $failureListeners = Vector{};
    private Vector<PassListener> $passListeners = Vector{};
    private Vector<RunEndListener> $runEndListeners = Vector{};
    private Vector<RunStartListener> $runStartListeners = Vector{};
    private Vector<SkipListener> $skipListeners = Vector{};
    private Vector<SuccessListener> $successListeners = Vector{};
    private Vector<SuiteEndListener> $suiteEndListeners = Vector{};
    private Vector<SuiteStartListener> $suiteStartListeners = Vector{};

    public function __construct(
        private (function(
            Vector<FailureListener>,
            Vector<SkipListener>,
            Vector<SuccessListener>,
        ):Assert) $assertBuilder,
    )
    {
    }

    public function onFailure(FailureListener $l) : this
    {
        $this->failureListeners->add($l);
        return $this;
    }

    public function onUncaughtException(ExceptionListener $l) : this
    {
        $this->exceptionListeners->add($l);
        return $this;
    }

    public function onPass(PassListener $l) : this
    {
        $this->passListeners->add($l);
        return $this;
    }

    public function onRunEnd(RunEndListener $l) : this
    {
        $this->runEndListeners->add($l);
        return $this;
    }

    public function onRunStart(RunStartListener $l) : this
    {
        $this->runStartListeners->add($l);
        return $this;
    }

    public function onSkip(SkipListener $l) : this
    {
        $this->skipListeners->add($l);
        return $this;
    }

    public function onSuccess(SuccessListener $l) : this
    {
        $this->successListeners->add($l);
        return $this;
    }

    public function onSuiteEnd(SuiteEndListener $l) : this
    {
        $this->suiteEndListeners->add($l);
        return $this;
    }

    public function onSuiteStart(SuiteStartListener $l) : this
    {
        $this->suiteStartListeners->add($l);
        return $this;
    }

    public function run(Vector<Suite> $suites) : void
    {

        // Throw an interruption after all other handlers
        $this->failureListeners->add($failure ==> {
            throw new Interruption();
        });

        $this->skipListeners->add($skip ==> {
            throw new Interruption();
        });
        $this->emitRunStart();

        try{
            foreach($suites as $s) {
                $s->setup();
                $this->emitSuiteStart();
                $this->runSuite($s);
                $this->emitSuiteEnd();
                $s->teardown();
            }
        } catch (\Exception $e) {
            foreach($this->exceptionListeners as $l) {
                $l($e);
            }
        }

        $this->emitRunEnd();
    }

    private function runSuite(Suite $s) : void
    {
        $builder = $this->assertBuilder;
        foreach($s->testCases() as $case) {
            $case->setup();
            try{
                $case->run($builder(
                    $this->failureListeners,
                    $this->skipListeners,
                    $this->successListeners,
                ));
            } catch (Interruption $e) {
                // Nothing to do anymore
            }
            $case->teardown();
            $this->emitPass();
        }
    }

    private function emitSuiteEnd() : void
    {
        foreach($this->suiteEndListeners as $l) {
            $l();
        }
    }

    private function emitSuiteStart() : void
    {
        foreach($this->suiteStartListeners as $l) {
            $l();
        }
    }

    private function emitRunEnd() : void
    {
        foreach($this->runEndListeners as $l) {
            $l();
        }
    }

    private function emitRunStart() : void
    {
        foreach($this->runStartListeners as $l) {
            $l();
        }
    }

    private function emitPass() : void
    {
        foreach($this->passListeners as $l) {
            $l();
        }
    }
}
