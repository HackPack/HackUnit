<?hh // strict

namespace HackPack\HackUnit;

use HackPack\HackUnit\Event\EndListener;
use HackPack\HackUnit\Event\ExceptionListener;
use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Event\MalformedSuiteListener;
use HackPack\HackUnit\Event\PassListener;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\StartListener;
use HackPack\HackUnit\Event\SuccessListener;

final class HackUnit
{
    private Vector<StartListener> $startListeners = Vector{};
    private Vector<EndListener> $endListeners = Vector{};

    private Vector<FailureListener> $failureListeners = Vector{};
    private Vector<PassListener> $passListeners = Vector{};
    private Vector<SkipListener> $skipListeners = Vector{};
    private Vector<SuccessListener> $successListeners = Vector{};
    private Vector<ExceptionListener> $untestedExceptionListeners = Vector{};

    public static function fromCli() : this
    {
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);

        $reporter = new Util\Reporter($clio);
        if($options->colors) {
            $reporter->withColor();
        }

        $suiteBuilder = ($fileName, $className, $skip) ==> {
            return new \HackPack\HackUnit\Test\SuiteImpl($fileName, $className, $skip);
        };
        $loader = new Util\Loader(
            $suiteBuilder,
            $options->includes,
            $options->excludes
        );

        $app = new static($loader);
        $app->onFailure(inst_meth($reporter, 'reportFailure'));
        $app->onSkip(inst_meth($reporter, 'reportSkip'));
        $app->onSuccess(inst_meth($reporter, 'reportSuccess'));
        $app->onPass(inst_meth($reporter, 'reportPass'));
        $app->onStart(inst_meth($reporter, 'startTiming'));
        $app->onFinish(inst_meth($reporter, 'displaySummary'));
        $app->onUntestedException(inst_meth($reporter, 'reportUntestedException'));
        $app->onMalformedSuite(inst_meth($reporter, 'reportMalformedSuite'));

        $reporter->identifyPackage();
        return $app;
    }

    public function __construct(
        private Util\Loader $loader,
    )
    {
    }

    public function run() : void
    {
        // Attach throwing listeners to the end of the stack
        // to interrupt flow of tests
        $this->failureListeners->add($failure ==> {
            throw new Exception\InterruptTest();
        });
        $this->skipListeners->add($skip ==> {
            throw new Exception\InterruptTest();
        });

        $this->start();
        foreach($this->loader->testSuites() as $suite) {
            try{
                $this->runSuite($suite);
            } catch (\Exception $e) {
                $this->emitUntestedException($e);
                exit(1);
            }
        }
        $this->finish();
    }

    private function runSuite(Test\Suite $suite) : void
    {
        $suite->registerSkipHandlers($this->skipListeners);
        $suite->setup();
        foreach($suite->cases() as $case) {
            $this->runTest($case);
        }
        $suite->teardown();
    }

    private function runTest(Test\TestCase $case) : void
    {
            $case->setup();
            try{
                $case->run($this->failureListeners, $this->skipListeners, $this->successListeners);
                $this->emitPass();
            } catch (Exception\InterruptTest $i) {
                // Other handlers should be done by now
            }
            $case->teardown();
    }

    public function onStart((function():void) $listener) : this
    {
        $this->startListeners->add($listener);
        return $this;
    }

    public function start() : void
    {
        foreach($this->startListeners as $l) {
            $l();
        }
    }

    public function onFinish((function():void) $listener) : this
    {
        $this->endListeners->add($listener);
        return $this;
    }

    public function finish() : void
    {
        foreach($this->endListeners as $l) {
            $l();
        }
    }

    public function onFailure((function(Failure):void) $listener) : this
    {
        $this->failureListeners->add($listener);
        return $this;
    }

    public function onPass((function():void) $listener) : this
    {
        $this->passListeners->add($listener);
        return $this;
    }

    public function emitPass() : void
    {
        foreach($this->passListeners as $l) {
            $l();
        }
    }

    public function onSkip((function(Skip):void) $listener) : this
    {
        $this->skipListeners->add($listener);
        return $this;
    }

    public function onSuccess((function():void) $listener) : this
    {
        $this->successListeners->add($listener);
        return $this;
    }

    public function onUntestedException((function(\Exception):void) $listener) : this
    {
        $this->untestedExceptionListeners->add($listener);
        return $this;
    }

    public function onMalformedSuite((function(MalformedSuite):void) $listener) : this
    {
        $this->loader->onMalformedSuite($listener);
        return $this;
    }

    public function emitUntestedException(\Exception $e) : void
    {
        foreach($this->untestedExceptionListeners as $l) {
            $l($e);
        }
    }
}
