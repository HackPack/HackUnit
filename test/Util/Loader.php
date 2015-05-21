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
    public function validateValidSuite(AssertionBuilder $assert) : void
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

        $suites = $loader->testSuites();
        $assert->context($suites->count())->identicalTo(1);

        $validSuite = $suites->at(0);

        $assert->context($validSuite->fileName())->identicalTo($this->fixturePath('/ValidSuite.php'));
        $assert->context($validSuite->className())->identicalTo(\HackPack\HackUnit\Tests\Fixtures\ValidSuite::class);
        $assert->context($validSuite->countSetup())->identicalTo(2);
        $assert->context($validSuite->countTeardown())->identicalTo(2);
        $assert->context($validSuite->countTestSetup())->identicalTo(4);
        $assert->context($validSuite->countTestTeardown())->identicalTo(4);
        $assert->context($validSuite->cases()->count())->identicalTo(2);
    }
}
