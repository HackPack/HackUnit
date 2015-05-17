<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;

final class ContextAssertion<Tcontext>
{
    use Assertion<Tcontext>;

    public function equalTo(mixed $expected) : void
    {
        $pass = $this->invert ?
            ($this->context == $expected) :
            ($this->context != $expected);
        if($pass) {
            $this->emitSuccess(new Success());
            return;
        }
        $this->emitFailure(new Failure());
    }

    public function identicalTo(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context === $expected) :
            ($this->context !== $expected);
        if($pass) {
            $this->emitSuccess(new Success());
            return;
        }
        $this->emitFailure(new Failure());
    }

    public function greaterThan(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context <= $expected) :
            ($this->context > $expected);
        if($pass) {
            $this->emitSuccess(new Success());
            return;
        }
        $this->emitFailure(new Failure());
    }

    public function lessThan(Tcontext $expected) : void
    {
        $pass = $this->invert ?
            ($this->context >= $expected) :
            ($this->context < $expected);
        if($pass) {
            $this->emitSuccess(new Success());
            return;
        }
        $this->emitFailure(new Failure());
    }

    public function contains(string $substring) : void
    {
        if(
            is_string($this->context) &&
            strpos($this->context, $substring) !== false
        ) {
            $this->emitSuccess(new Success());
        }
        $this->emitFailure(new Failure());
    }
}
