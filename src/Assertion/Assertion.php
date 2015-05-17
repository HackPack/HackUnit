<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;

trait Assertion<Tcontext>
{
    private bool $invert = false;
    private Tcontext $context;
    private Vector<(function(Failure):void)> $failureListeners;
    private Vector<(function(Skip):void)> $skipListeners;
    private Vector<(function(Success):void)> $successListeners;

    public function __construct(
        Tcontext $context,
        Vector<(function(Failure):void)> $failureListeners,
        Vector<(function(Skip):void)> $skipListeners,
        Vector<(function(Success):void)> $successListeners,
    )
    {
        $this->context = $context;
        $this->failureListeners = $failureListeners;
        $this->skipListeners = $skipListeners;
        $this->successListeners = $successListeners;
    }

    public function isNot() : this
    {
        return $this->not();
    }

    public function willNot() : this
    {
        return $this->not();
    }

    public function not() : this
    {
        $this->invert = true;
        return $this;
    }

    private function emitSuccess(Success $e) : void
    {
        $this->successListeners->map($l ==> {
            $l($e);
            return $l;
        });
    }

    private function emitFailure(Failure $e) : void
    {
        $this->failureListeners->map($l ==> {
            $l($e);
            return $l;
        });
    }

    public function skip() : void
    {
        $event = new Skip();
        $this->skipListeners->map($l ==> {
            $l($event);
            return $l;
        });
    }
}
