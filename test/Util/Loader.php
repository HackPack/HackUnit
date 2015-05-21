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
        $path = realpath(dirname(__DIR__) . '/Fixtures' . $extra);
        if(is_string($path)) {
            return $path;
        }
        throw new \Exception('Unable to load ' . $extra);
    }

    private function loadFile(string $include) : (Vector<Suite>, Vector<MalformedSuite>)
    {
        $errors = Vector{};
        $loader = new Loader();
        $loader
            ->including($include)
            ->onMalformedSuite((MalformedSuite $event) ==> {
                $errors->add($event);
            });
        $suites = $loader->testSuites();
        return tuple($suites, $errors);
    }

    private function invalidClass(string $className) : string
    {
        return 'HackPack\HackUnit\Tests\Fixtures\InvalidSuites\\' . $className;
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
        $assert->context($event->line())->identicalTo(8);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('ConstructorParams'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function constructorCannotBeTest(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/ConstructorTest.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('ConstructorTest'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function destructorCannotBeTest(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/DestructorTest.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__destruct');
        $assert->context($event->className())->identicalTo($this->invalidClass('DestructorTest'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function missingTestAnnotations(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/MissingTestAnnotation.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->isNull();
        $assert->context($event->method())->isNull();
        $assert->context($event->className())->identicalTo($this->invalidClass('MissingTestAnnotation'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function setupParams(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SetupParams.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setup');
        $assert->context($event->className())->identicalTo($this->invalidClass('SetupParams'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }
}
