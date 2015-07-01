<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

<<nocover>>
class Failure
{
    public function __construct(
        private string $message,
        private TraceItem $callSite,
    )
    {
    }

    public function testMethod() : ?string
    {
        return $this->callSite['function'];
    }

    public function testClass() : ?string
    {
        return $this->callSite['class'];
    }

    public function assertionLine() : ?int
    {
        return $this->callSite['line'];
    }

    public function testFile() : ?string
    {
        return $this->callSite['file'];
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}
