<?hh // strict

namespace HackPack\HackUnit\Event;

trait FailureEmitter
{
    private Vector<FailureListener> $failureListeners = Vector{};

    public function onFailure(FailureListener $l) : this
    {
        $this->failureListeners->add($l);
        return $this;
    }

    public function setFailureListeners(Traversable<FailureListener> $listeners) : this
    {
        $this->failureListeners->clear()->setAll(new Vector($listeners));
        return $this;
    }

    private function emitFailure(Failure $event) : void
    {
        foreach($this->failureListeners as $l) {
            $l($event);
        }
    }
}
