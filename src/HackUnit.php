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
    private Vector<(function(Skip):void)> $skipListeners = Vector{};
    private Vector<(function(Success):void)> $successListeners = Vector{};

    public static function fromCli() : this
    {
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);
        $loader = new Util\Loader($options->includes, $options->excludes);
        $reporter = new Util\Reporter($clio);
        if($options->colors) {
            $reporter->withColor();
        }
        $app = new static($loader);
        $app->onFailure(inst_meth($reporter, 'reportFailure'));
        $app->onSkip(inst_meth($reporter, 'reportSkip'));
        $app->onSuccess(inst_meth($reporter, 'reportSuccess'));
        $app->onStart(inst_meth($reporter, 'startTiming'));
        $app->onFinish(inst_meth($reporter, 'displaySummary'));
        return $app;
    }

    public function __construct(
        private Util\Loader $loader,
    )
    {
    }

    public function run() : void
    {
        $assertionBuilder = new Assertion\AssertionBuilder(
            $this->failureListeners,
            $this->skipListeners,
            $this->successListeners,
        );

        $this->start();
        foreach($this->loader->testSuites() as $suite) {
            $suite->setup();
            foreach($suite->cases() as $case) {
                $case->setup();
                $case->run($assertionBuilder);
                $case->teardown();
            }
            $suite->teardown();
        }
        $this->finish();
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
}
