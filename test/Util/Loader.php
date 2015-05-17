<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Util\Loader;

class LoaderTest
{
    <<__Memoize>>
    private function fixurePath() : string
    {
        return realpath(dirname(__DIR__) . '/Fixtures');
    }

    <<test>>
    public function throwIt(AssertionBuilder $assert) : void
    {
    }

    <<test>>
    public function doNotThrow(AssertionBuilder $assert) : void
    {
    }
}
