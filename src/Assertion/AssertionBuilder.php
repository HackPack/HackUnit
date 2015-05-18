<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;

class AssertionBuilder
{

    public function __construct(
        private Vector<(function(Failure):void)> $failureListeners,
        private Vector<(function(Skip):void)> $skipListeners,
        private Vector<(function():void)> $successListeners,
    )
    {
    }

    public function context<Tcontext>(Tcontext $context) : ContextAssertion<Tcontext>
    {
        return new ContextAssertion(
            $context,
            $this->failureListeners,
            $this->skipListeners,
            $this->successListeners,
        );
    }

    public function whenCalled((function():void) $context) : CallableAssertion
    {
        return new CallableAssertion(
            $context,
            $this->failureListeners,
            $this->skipListeners,
            $this->successListeners,
        );
    }
}
