<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class AssertionBuilder
{

    private ?\ReflectionMethod $testMethod = null;

    public function __construct(
        private Vector<FailureListener> $failureListeners,
        private Vector<SkipListener> $skipListeners,
        private Vector<SuccessListener> $successListeners,
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
