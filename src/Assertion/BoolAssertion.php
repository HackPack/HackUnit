<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureEmitter;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SuccessEmitter;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class BoolAssertion implements \HackPack\HackUnit\Contract\Assertion\BoolAssertion
{
    use FailureEmitter;
    use SuccessEmitter;

    public function __construct(
        private bool $context,
        Vector<FailureListener> $failureListeners,
        Vector<SuccessListener> $successListeners,
    )
    {
        $this->setFailureListeners($failureListeners);
        $this->setSuccessListeners($successListeners);
    }

    public function is(bool $expected) : void
    {
        if($this->context === $expected) {
            $this->emitSuccess();
            return;
        }
        $this->emitFailure(Failure::fromCallStack(
            'Expected ' . ($expected ? 'true' : 'false') . ', value was ' . ($expected ? 'false' : 'true') . '.',
        ));
    }
}
