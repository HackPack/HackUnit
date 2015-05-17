<?hh // strict

namespace HackPack\HackUnit;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;

final class HackUnit
{
    private Vector<(function():void)> $startListeners = Vector{};
    private Vector<(function():void)> $endListeners = Vector{};

    private Vector<(function(Failure):void)> $failureListeners = Vector{};
    private Vector<(function():void)> $passListeners = Vector{};
    private Vector<(function(Skip):void)> $skipListeners = Vector{};
    private Vector<(function(Success):void)> $successListeners = Vector{};
    private Vector<(function(\Exception):void)> $untestedExceptionListeners = Vector{};

    public static function fromCli() : this
    {
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);

        $reporter = new Util\Reporter($clio);
        if($options->colors) {
            $reporter->withColor();
        }

        $loader = new Util\Loader($options->includes, $options->excludes);

        $app = new static($loader);
        $app->onFailure(inst_meth($reporter, 'reportFailure'));
        $app->onSkip(inst_meth($reporter, 'reportSkip'));
        $app->onSuccess(inst_meth($reporter, 'reportSuccess'));
        $app->onPass(inst_meth($reporter, 'reportPass'));
        $app->onStart(inst_meth($reporter, 'startTiming'));
        $app->onFinish(inst_meth($reporter, 'displaySummary'));
        $app->onUntestedException(inst_meth($reporter, 'reportUntestedException'));
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

        $assertionBuilder = new Assertion\AssertionBuilder(
            $this->failureListeners,
            $this->skipListeners,
            $this->successListeners,
        );

        $this->start();
        foreach($this->loader->testSuites() as $suite) {
            try{
                $this->runSuite($suite, $assertionBuilder);
            } catch (\Exception $e) {
                $this->emitUntestedException($e);
            }
        }
        $this->finish();
    }

    private function runSuite(Test\Suite $suite, Assertion\AssertionBuilder $builder) : void
    {
        $suite->setup();
        foreach($suite->cases() as $case) {
            try{
                $this->runTest($case, $builder);
            } catch (\Exception $e) {
                $this->emitUntestedException($e);
            }
        }
        $suite->teardown();
    }

    private function runTest(Test\TestCase $case, Assertion\AssertionBuilder $builder) : void
    {
            $case->setup();
            try{
                $case->run($builder);
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

    public function onSuccess((function(Success):void) $listener) : this
    {
        $this->successListeners->add($listener);
        return $this;
    }

    public function onUntestedException((function(\Exception):void) $listener) : this
    {
        $this->untestedExceptionListeners->add($listener);
        return $this;
    }

    public function emitUntestedException(\Exception $e) : void
    {
        foreach($this->untestedExceptionListeners as $l) {
            $l($e);
        }
    }
}
