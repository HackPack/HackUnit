<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;

<<__ConsistentConstruct>>
class CallableAssertion
{
    use Assertion<(function():void)>;

    public function __construct(
        private (function():void) $context,
        private Vector<(function(Failure):void)> $failureListeners,
        private Vector<(function(Skip):void)> $skipListeners,
        private Vector<(function(Success):void)> $successListeners,
    )
    {
    }

    public function willThrow(?string $exceptionName = null, ?string $message = null) : void
    {
        try{
            $c = $this->context;
            $c();
        } catch (\Exception $e) {
            if($this->invert){
                // Didn't expect the exception
                $this->emitFailure(new Failure());
                return;
            }

            if($exceptionName !== null && ! is_a($e, $exceptionName)) {
                $this->emitFailure(new Failure());
                return;
            }

            if($message !== null && $e->getMessage() !== $message) {
                $this->emitFailure(new Failure());
                return;
            }

            $this->emitSuccess(new Success());
            return;
        }

        // Context is not callable or did not throw
        $this->invert ?
            $this->emitSuccess(new Success()) :
            $this->emitFailure(new Failure());
    }
}
