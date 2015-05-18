<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;

final class ContextAssertion<Tcontext>
{
    use Assertion<Tcontext>;

    public function equalTo(mixed $expected) : void
    {
        $pass = $this->invert ?
            ($this->context == $expected) :
            ($this->context != $expected);
        if($pass) {
            $this->emitSuccess();
            return;
        }
        $message = sprintf(
            'Assertion failed.  Expected %s to %s %s.',
            (string)$this->context,
            $this->invert ? 'not equal' : 'equal',
            (string)$expected,
        );
        $this->emitFailure($message);
    }

    public function identicalTo(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context === $expected) :
            ($this->context !== $expected);
        if($pass) {
            $this->emitSuccess();
            return;
        }
        $message = sprintf(
            'Assertion failed.  Expected %s to %s %s.',
            (string)$this->context,
            $this->invert ? 'not to be identical to' : 'to be identical to',
            (string)$expected,
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
        $message = sprintf(
            'Assertion failed.  Expected %s to %s %s.',
            (string)$this->context,
            $this->invert ? 'not be greater than' : 'to be greater than',
            (string)$expected,
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
        $message = sprintf(
            'Assertion failed.  Expected %s to %s %s.',
            (string)$this->context,
            $this->invert ? 'not be less than' : 'to be less than',
            (string)$expected,
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
        $message = sprintf(
            'Assertion failed.  Expected %s to %s %s.',
            (string)$this->context,
            $this->invert ? 'not contain' : 'to contain',
            (string)$substring,
        );
        $this->emitFailure($message);
    }
}
