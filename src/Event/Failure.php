<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

class Failure
{
    public function __construct(
        private string $message,
        private mixed $context,
        private Vector<TraceItem> $backtrace,
        private ?\ReflectionMethod $testMethod,
    )
    {
    }

    public function testFile() : string
    {
        if($this->testMethod === null) {
            return '';
        }

        return (string)$this->testMethod->getFileName();
    }

    public function testMethod() : string
    {
        if($this->testMethod === null) {
            return '';
        }
        return $this->testMethod->getName();
    }

    public function context() : mixed
    {
        return $this->context;
    }

    public function assertionLine() : int
    {
        return Trace::findAssertionLine($this->backtrace);
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}
