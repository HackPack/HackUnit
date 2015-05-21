<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Util\Trace;

class AssertionBuilder
{

    private ?\ReflectionMethod $testMethod = null;

    public function __construct(
        private Vector<(function(Failure):void)> $failureListeners,
        private Vector<(function(Skip):void)> $skipListeners,
        private Vector<(function():void)> $successListeners,
    )
    {
    }

    public function setMethod(\ReflectionMethod $method) : void
    {
        $this->testMethod = $method;
    }

    public function context<Tcontext>(Tcontext $context) : ContextAssertion<Tcontext>
    {
        return new ContextAssertion(
            $context,
            $this->failureListeners,
            $this->skipListeners,
            $this->successListeners,
            $this->testMethod,
        );
    }

    public function whenCalled((function():void) $context) : CallableAssertion
    {
        return new CallableAssertion(
            $context,
            $this->failureListeners,
            $this->skipListeners,
            $this->successListeners,
            $this->testMethod,
        );
    }

    public function skip() : void
    {
        // Caller is one up from here
        $trace = Trace::generate();
        $event = new Skip(Trace::buildItem([
            'line' => $trace->at(0)['line'],
            'function' => $trace->at(1)['function'],
            'class' => $trace->at(1)['class'],
            'file' => $trace->at(0)['file'],
        ]));
        foreach($this->skipListeners as $l) {
            $l($event);
        }
    }
}
