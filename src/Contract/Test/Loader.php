<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Contract\Test\Suite;
use HackPack\HackUnit\Event\MalformedSuiteListener;

interface Loader
{
    public function onMalformedSuite(MalformedSuiteListener $listener) : this;
    public function including(string $path) : this;
    public function excluding(string $path) : this;
    public function testSuites() : Vector<Suite>;
}
