<?hh // strict

namespace HackPack\HackUnit;

final class Assert implements Contract\Assert
{
    <<nocover>>
    public static function build(
        Vector<Event\FailureListener> $failureListeners,
        Vector<Event\SkipListener> $skipListeners,
        Vector<Event\SuccessListener> $successListeners,
    ) : this
    {
        return new static($failureListeners, $skipListeners, $successListeners);
    }

    <<nocover>>
    public function __construct(
        private Vector<Event\FailureListener> $failureListeners,
        private Vector<Event\SkipListener> $skipListeners,
        private Vector<Event\SuccessListener> $successListeners,
    )
    {
    }

    <<nocover>>
    public function bool(bool $context) : Contract\Assertion\BoolAssertion
    {
        return new Assertion\BoolAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<nocover>>
    public function int(int $context) : Contract\Assertion\NumericAssertion<int>
    {
        return new Assertion\NumericAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<nocover>>
    public function float(float $context) : Contract\Assertion\NumericAssertion<float>
    {
        return new Assertion\NumericAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<nocover>>
    public function string(string $context) : Contract\Assertion\StringAssertion
    {
        return new Assertion\StringAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<nocover>>
    public function whenCalled((function():void) $context) : Contract\Assertion\CallableAssertion
    {
        return new Assertion\CallableAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<nocover>>
    public function mixed(mixed $context) : Contract\Assertion\MixedAssertion
    {
        return new Assertion\MixedAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function skip(string $reason, ?Util\TraceItem $traceItem = null) : void
    {
        if($traceItem === null) {
            // Assume the caller was a test method
            $stack = Util\Trace::generate();
            $traceItem = shape(
                'file' => $stack[0]['file'],
                'line' => $stack[1]['line'],
                'function' => $stack[1]['function'],
                'class' => $stack[0]['class'],
            );
        }
        $skip = new Event\Skip($reason, $traceItem);
        foreach($this->skipListeners as $l) {
            $l($skip);
        }
    }
}
