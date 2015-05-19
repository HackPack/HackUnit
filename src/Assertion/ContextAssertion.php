<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;

final class ContextAssertion<Tcontext>
{
    use Assertion<Tcontext>;

    public function equalTo(mixed $expected) : void
    {
        $pass = $this->invert ?
            ($this->context != $expected) :
            ($this->context == $expected);
        if($pass) {
            $this->emitSuccess();
            return;
        }
        $message = $this->constructErrorMessage(
            $expected,
            $this->invert ? 'to not equal' : 'to equal',
        );
        $this->emitFailure($message);
    }

    public function identicalTo(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context !== $expected) :
            ($this->context === $expected);
        if($pass) {
            $this->emitSuccess();
            return;
        }
        $message = $this->constructErrorMessage(
            $expected,
            $this->invert ? 'to not be identical to' : 'to be identical to',
        );
        $this->emitFailure($message);
    }

    public function greaterThan(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context <= $expected) :
            ($this->context > $expected);
        if($pass) {
            $this->emitSuccess();
            return;
        }
        $message = $this->constructErrorMessage(
            $expected,
            $this->invert ? 'to be less than or equal to' : 'to be greater than',
        );
        $this->emitFailure($message);
    }

    public function lessThan(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context >= $expected) :
            ($this->context < $expected);
        if($pass) {
            $this->emitSuccess();
            return;
        }
        $message = $this->constructErrorMessage(
            $expected,
            $this->invert ? 'to be greater than or equal to' : 'to be less than',
        );
        $this->emitFailure($message);
    }

    public function contains(string $substring) : void
    {
        if( ! is_string($this->context)) {
            if(is_object($this->context)){
                $ctype = get_class($this->context);
            } else {
                $ctype = gettype($this->context);
            }
            $this->emitFailure('Contains assertion is only valid for string contexts. ' . $ctype . ' provided.');
            return;
        }
        if(
            is_string($this->context) &&
            strpos($this->context, $substring) !== false
        ) {
            $this->emitSuccess();
        }
        $message = $this->constructErrorMessage(
            $substring,
            $this->invert ? 'not to contain' : 'to contain',
        );
        $this->emitFailure($message);
    }

    private function constructErrorMessage(mixed $expected, string $expectation) : string
    {
        return sprintf(
            'Expected %s %s %s %s %s.',
            gettype($this->context),
            (string)$this->context,
            $expectation,
            gettype($expected),
            (string)$expected,
        );
    }
}
