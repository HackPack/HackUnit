<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Suite;
use HackPack\HackUnit\Util\Loader;

<<TestSuite>>
class LoaderTest
{
    <<Setup>>
    public function resetCounts() : void
    {
    }

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

    /**
     * Suite setup structure
     */

    <<Test>>
    public function suiteSetupMethodsMustHaveOneParameter(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteSetup/Params.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setup');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteSetup\Params'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function staticMethodsCannotBeSuiteSetups(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteSetup/StaticMethods.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setItUp');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteSetup\StaticMethods'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function destructorCannotBeSuiteSetup(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteSetup/Destructor.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__destruct');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteSetup\Destructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function constructorCannotBeSuiteSetup(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteSetup/Constructor.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteSetup\Constructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    /**
     * Suite teardown structure
     */

    <<Test>>
    public function suiteTeardownMethodsMustHaveOneParameter(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteTeardown/Params.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setup');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteTeardown\Params'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function staticMethodsCannotBeSuiteTeardowns(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteTeardown/StaticMethods.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('tearItDown');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteTeardown\StaticMethods'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function destructorCannotBeSuiteTeardown(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteTeardown/Destructor.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__destruct');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteTeardown\Destructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function constructorCannotBeSuiteTeardown(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/SuiteTeardown/Constructor.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('SuiteTeardown\Constructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    /**
     * Test teardown structure
     */

    <<Test>>
    public function testTeardownMethodsMustHaveOneParameter(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestTeardown/Params.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setup');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestTeardown\Params'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function staticMethodsCannotBeTestTeardowns(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestTeardown/StaticMethods.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('tearItDown');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestTeardown\StaticMethods'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function destructorCannotBeTestTeardown(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestTeardown/Destructor.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__destruct');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestTeardown\Destructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function constructorCannotBeTestTeardown(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestTeardown/Constructor.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestTeardown\Constructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    /**
     * Test setup structure
     */

    <<Test>>
    public function testSetupMethodsMustHaveOneParameter(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestSetup/Params.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setup');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestSetup\Params'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function staticMethodsCannotBeTestSetups(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestSetup/StaticMethods.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setItUp');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestSetup\StaticMethods'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function destructorCannotBeTestSetup(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestSetup/Destructor.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__destruct');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestSetup\Destructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function constructorCannotBeTestSetup(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/TestSetup/Constructor.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('TestSetup\Constructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    /**
     * Test method structure
     */

    <<Test>>
    public function testMethodsCannotHaveZeroParams(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/Test/TooFewParams.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(1);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('test');
        $assert->context($event->className())->identicalTo($this->invalidClass('Test\TooFewParams'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function testMethodsCannotHaveTwoParameters(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/Test/TooManyParams.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(1);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('test');
        $assert->context($event->className())->identicalTo($this->invalidClass('Test\TooManyParams'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function staticMethodsCannotBeTests(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/Test/StaticMethods.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('setItUp');
        $assert->context($event->className())->identicalTo($this->invalidClass('Test\StaticMethods'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function destructorCannotBeTest(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/Test/Destructor.php');
        list($suites, $error) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($error->count())->identicalTo(1);
        $event = $error->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__destruct');
        $assert->context($event->className())->identicalTo($this->invalidClass('Test\Destructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }

    <<Test>>
    public function constructorCannotBeTest(AssertionBuilder $assert) : void
    {
        $fileName = $this->fixturePath('/InvalidSuites/Test/Constructor.php');
        list($suites, $errors) = $this->loadFile($fileName);

        $assert->context($suites->count())->identicalTo(0);
        $assert->context($errors->count())->identicalTo(1);
        $event = $errors->at(0);
        $assert->context($event->line())->identicalTo(9);
        $assert->context($event->method())->identicalTo('__construct');
        $assert->context($event->className())->identicalTo($this->invalidClass('Test\Constructor'));
        $assert->context($event->fileName())->identicalTo($fileName);
    }
}
