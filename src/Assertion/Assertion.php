<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;

trait Assertion<Tcontext>
{
    private bool $invert = false;
    private Tcontext $context;
    private Vector<(function(Failure):void)> $failureListeners;
    private Vector<(function(Skip):void)> $skipListeners;
    private Vector<(function():void)> $successListeners;

    public function __construct(
        Tcontext $context,
        Vector<(function(Failure):void)> $failureListeners,
        Vector<(function(Skip):void)> $skipListeners,
        Vector<(function():void)> $successListeners,
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

    private function emitSuccess() : void
    {
        foreach($this->successListeners as $l) {
            $l();
        }
    }

    private function emitFailure(Failure $e) : void
    {
        foreach($this->failureListeners as $l) {
            $l($e);
        }
    }

    public function skip(Skip $e) : void
    {
        foreach($this->skipListeners as $l) {
            $l($e);
        }
    }
}
