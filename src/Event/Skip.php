<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\TraceItem;

class Skip
{
    public function __construct(private \ReflectionMethod $testMethod)
    {
    }

    public function testMethod() : string
    {
        return $this->testMethod->class . ' -> ' . $this->testMethod->name;
    }

    public function testFile() : string
    {
        return (string)$this->testMethod->getFileName();
    }
}
