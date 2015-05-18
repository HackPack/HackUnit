<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Util\Loader;

<<TestSuite>>
class LoaderTest
{
    private function fixturePath(string $extra) : string
    {
        return realpath(dirname(__DIR__) . '/Fixtures' . $extra);
    }

    <<Test>>
    public function throwIt(AssertionBuilder $assert) : void
    {
        $errors = Vector{};
        $loader = new Loader();
        $loader
            ->including($this->fixturePath('/ValidSuite.php'))
            ->onMalformedSuite((MalformedSuite $event) ==> {
                $errors->add($event);
            })
            ;
        $assert->context($errors->count())->identicalTo(0);
    }

    <<Test>>
    public function doNotThrow(AssertionBuilder $assert) : void
    {
        throw new \Exception('Look at this!');
    }
}
