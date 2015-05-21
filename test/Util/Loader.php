<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Suite;
use HackPack\HackUnit\Util\Loader;

<<TestSuite>>
class LoaderTest
{
    private function fixturePath(string $extra) : string
    {
        return realpath(dirname(__DIR__) . '/Fixtures' . $extra);
    }

    private function loadFile(string $include) : (Vector<Suite>, Vector<MalformedSuite>)
    {
        $errors = Vector{};
        $loader = new Loader();
        $loader
            ->including($this->fixturePath('/ValidSuite.php'))
            ->onMalformedSuite((MalformedSuite $event) ==> {
                $errors->add($event);
            });
        $suites = $loader->testSuites();
        return tuple($suites, $errors);
    }

    <<Test>>
    public function validateValidSuite(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/ValidSuite.php');
        list($suites, $errors) = $this->loadFile($fileName);
        $assert->context($suites->count())->identicalTo(1);
        $assert->context($errors->count())->identicalTo(0);

        $validSuite = $suites->at(0);

        $assert->context($validSuite->fileName())->identicalTo($fileName);
        $assert->context($validSuite->className())->identicalTo(\HackPack\HackUnit\Tests\Fixtures\ValidSuite::class);
        $assert->context($validSuite->countSetup())->identicalTo(2);
        $assert->context($validSuite->countTeardown())->identicalTo(2);
        $assert->context($validSuite->countTestSetup())->identicalTo(4);
        $assert->context($validSuite->countTestTeardown())->identicalTo(4);
        $assert->context($validSuite->cases()->count())->identicalTo(2);
    }

    <<Test>>
    public function constructorCannotRequireParams(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/ConstructorParams.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->fileName())->identicalTo($fileName);
        $assert->context($event->method())->identicalTo('__construct');
    }

    <<Test>>
    public function constructorCannotBeTest(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/ConstructorTest.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->fileName())->identicalTo($fileName);
        $assert->context($event->method())->identicalTo('__construct');
    }
}
