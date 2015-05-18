<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Util\Trace;

<<__ConsistentConstruct>>
class CallableAssertion
{
    use Assertion<(function():void)>;

    public function __construct(
        private (function():void) $context,
        private Vector<(function(Failure):void)> $failureListeners,
        private Vector<(function(Skip):void)> $skipListeners,
        private Vector<(function():void)> $successListeners,
        private ?\ReflectionMethod $testMethod,
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
                $msg = 'Exception assertion failed. Unexpected exception ' . get_class($e) . ' thrown on ' . $e->getFile() . ' line ' . $e->getLine();
                $this->emitFailure($msg);
                return;
            }

            if($exceptionName !== null && ! is_a($e, $exceptionName)) {
                $msg = 'Exception assertion failed.  Expected exception type ' . $exceptionName . ' actual type ' . get_class($e);
                $this->emitFailure($msg);
                return;
            }

            if($message !== null && $e->getMessage() !== $message) {
                $msg = 'Exception assertion failed.  Expected exception message ' . $message . ' actual message ' . $e->getMessage();
                $this->emitFailure($msg);
                return;
            }

            $this->emitSuccess();
            return;
        }

        // Context is not callable or did not throw
        if($this->invert){
            $this->emitSuccess();
            return;
        }
        $msg = 'Exception assertion failed.  Expected exception but none was thrown.';
        $this->emitFailure($msg);
    }
}
