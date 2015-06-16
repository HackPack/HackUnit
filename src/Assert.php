<?hh // strict

namespace HackPack\HackUnit;

final class Assert implements Contract\Assert
{
    public static function build(
        Vector<Event\FailureListener> $failureListeners,
        Vector<Event\SkipListener> $skipListeners,
        Vector<Event\SuccessListener> $successListeners,
    ) : this
    {
        return new static($failureListeners, $skipListeners, $successListeners);
    }
    public function __construct(
        private Vector<Event\FailureListener> $failureListeners,
        private Vector<Event\SkipListener> $skipListeners,
        private Vector<Event\SuccessListener> $successListeners,
    )
    {
    }

    public function bool(bool $context) : Contract\Assertion\BoolAssertion
    {
        return new Assertion\BoolAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function int(int $context) : Contract\Assertion\NumericAssertion<int>
    {
        return new Assertion\NumericAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function float(float $context) : Contract\Assertion\NumericAssertion<float>
    {
        return new Assertion\NumericAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function string(string $context) : Contract\Assertion\StringAssertion
    {
        return new Assertion\StringAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function whenCalled((function():void) $context) : Contract\Assertion\CallableAssertion
    {
        return new Assertion\CallableAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function skip(string $reason, ?Util\TraceItem $traceItem = null) : void
    {
    }
}
