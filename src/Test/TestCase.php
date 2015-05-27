<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Event\MalformedSuiteListener;

class TestCase
{
    private Vector<SkipListener> $skipListeners = Vector{};

    public function __construct(
        private (function():void) $setup,
        private (function(AssertionBuilder):void) $test,
        private (function():void) $teardown,
        private bool $skip,
        private (function():Skip) $skipEventBuilder,
    )
    {
    }

    public function setup() : void
    {
        $f = $this->setup;
        $f();
    }

    public function teardown() : void
    {
        $f = $this->teardown;
        $f();
    }

    public function run(
        Vector<FailureListener> $failureListeners,
        Vector<SkipListener> $skipListeners,
        Vector<SuccessListener> $successListeners,
    ) : void
    {
        if($this->skip) {
            $builder = $this->skipEventBuilder;
            $e = $builder();
            foreach($this->skipListeners as $l) {
                $l($e);
            }
            return;
        }
        $f = $this->test;
        $f(new AssertionBuilder($failureListeners, $skipListeners, $successListeners));
    }
}
