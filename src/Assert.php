<?hh // strict

namespace HackPack\HackUnit;

final class Assert implements Contract\Assert
{
    <<IgnoreCoverage>>
    public static function build(
        Vector<Event\FailureListener> $failureListeners,
        Vector<Event\SkipListener> $skipListeners,
        Vector<Event\SuccessListener> $successListeners,
    ) : this
    {
        return new static($failureListeners, $skipListeners, $successListeners);
    }

    <<IgnoreCoverage>>
    public function __construct(
        private Vector<Event\FailureListener> $failureListeners,
        private Vector<Event\SkipListener> $skipListeners,
        private Vector<Event\SuccessListener> $successListeners,
    )
    {
    }

    <<IgnoreCoverage>>
    public function bool(bool $context) : Contract\Assertion\BoolAssertion
    {
        return new Assertion\BoolAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<IgnoreCoverage>>
    public function int(int $context) : Contract\Assertion\NumericAssertion<int>
    {
        return new Assertion\NumericAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<IgnoreCoverage>>
    public function float(float $context) : Contract\Assertion\NumericAssertion<float>
    {
        return new Assertion\NumericAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<IgnoreCoverage>>
    public function string(string $context) : Contract\Assertion\StringAssertion
    {
        return new Assertion\StringAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<IgnoreCoverage>>
    public function whenCalled((function():void) $context) : Contract\Assertion\CallableAssertion
    {
        return new Assertion\CallableAssertion($context, $this->failureListeners, $this->successListeners);
    }

    <<IgnoreCoverage>>
    public function mixed(mixed $context) : Contract\Assertion\MixedAssertion
    {
        return new Assertion\MixedAssertion($context, $this->failureListeners, $this->successListeners);
    }

    public function skip(string $reason, ?Util\TraceItem $traceItem = null) : void
    {
        if($traceItem === null) {
            // Assume the caller was a test method
            $stack = Util\Trace::generate();
            /* IgnoreCoverage */
            $traceItem = shape(
                'file' => $stack[0]['file'],
                'line' => $stack[0]['line'],
                'function' => $stack[1]['function'],
                'class' => $stack[1]['class'],
            );
        }
        $skip = new Event\Skip($reason, $traceItem);
        foreach($this->skipListeners as $l) {
            $l($skip);
        }
    }
}
