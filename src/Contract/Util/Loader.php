<?hh // strict

namespace HackPack\HackUnit\Contract\Util;

use HackPack\HackUnit\Contract\Test\Suite;
use HackPack\HackUnit\Event\MalformedSuite;

interface Loader
{
    public function onMalformedSuite((function(MalformedSuite):void) $listener) : this;
    public function including(string $path) : this;
    public function excluding(string $path) : this;
    public function testSuites() : Vector<Suite>;
}
