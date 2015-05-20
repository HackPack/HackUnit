<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\TraceItem;

class Skip
{
    public function __construct(private TraceItem $callSite)
    {
    }

    public function testMethod() : ?string
    {
        return $this->callSite['function'] === '' ?
            null :
            $this->callSite['function'];
    }

    public function testClass() : ?string
    {
        return $this->callSite['class'] === '' ?
            null :
            $this->callSite['class'];
    }

    public function testFile() : ?string
    {
        return $this->callSite['file'] === '' ?
            null :
            $this->callSite['file'];
    }

    public function assertionLine() : ?int
    {
        return $this->callSite['line'] === -1 ?
            null :
            $this->callSite['line'];
    }
}

