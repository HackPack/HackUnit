<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Loader;
use HackPack\HackUnit\Tests\Mocks\Test\Suite;

newtype UpDownFailure = shape(
    'line' => int,
    'method' => string,
    'class' => string,
    'file' => string,
);

<<TestSuite>>
class InvalidLoaderTest
{
    private Vector<MalformedSuite> $malformedEvents = Vector{};
    private (function(\ReflectionClass):Suite) $suiteBuilder;
    private Loader $loader;
    private string $fixturesPath;

    private Vector<UpDownFailure> $failurePoints = Vector{
        shape(
            'file' => 'Constructor.php',
            'class' => 'Constructor',
            'method' => '__construct',
            'line' => 9,
        ),
        shape(
            'file' => 'Destructor.php',
            'class' => 'Destructor',
            'method' => '__destruct',
            'line' => 9,
        ),
        shape(
            'file' => 'Params.php',
            'class' => 'Params',
            'method' => 'params',
            'line' => 9,
        ),
        shape(
            'file' => 'StaticMethods.php',
            'class' => 'StaticMethods',
            'method' => 'noStatics',
            'line' => 9,
        ),
    };

    public function __construct()
    {
        $this->suiteBuilder = $mirror ==> new Suite($mirror);
        $this->loader = new Loader($this->suiteBuilder);
        $this->loader->onMalformedSuite($e ==> {$this->malformedEvents->add($e);});
        $this->fixturesPath = dirname(__DIR__) . '/Fixtures/InvalidSuites/';
    }

    private function suitePath(string $path) : string
    {
        return $this->fixturesPath . ltrim($path, '/');
    }

    private function suiteName(string $name) : string
    {
        return 'HackPack\HackUnit\Tests\Fixtures\InvalidSuites\\' . $name;
    }

    <<Setup>>
    public function resetLoader() : void
    {
        $this->loader = new Loader($this->suiteBuilder);
        $this->loader->onMalformedSuite($e ==> {$this->malformedEvents->add($e);});
        $this->malformedEvents->clear();
    }

    private function testFailurePoints(string $name, Assert $assert) : void
    {
        $fullName = $this->suiteName($name);
        $fullPath = rtrim($this->suitePath($name), '/');

        $this->loader->including($fullPath);
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(4);

        foreach($this->failurePoints as $index => $failure) {
            $event = $this->malformedEvents->at($index);
            $line = $event->line();
            $method = $event->method();
            $class = $event->className();
            $file = $event->fileName();

            $assert->mixed($line)->isInt();
            invariant(is_int($line), '');
            $assert->mixed($method)->isString();
            invariant(is_string($method), '');
            $assert->mixed($class)->isString();
            invariant(is_string($class), '');
            $assert->mixed($file)->isString();
            invariant(is_string($file), '');

            $assert->int($line)->eq($failure['line']);
            $assert->string($method)->is($failure['method']);
            $assert->string($class)->is($this->suiteName($name) . '\\' . $failure['class']);
            $assert->string($file)->is($fullPath . '/' . $failure['file']);
        }
    }

    <<Test>>
    public function invalidTestSetupMethods(Assert $assert) : void
    {
        $this->testFailurePoints('TestSetup', $assert);
    }

    <<Test>>
    public function invalidTestTeardownMethods(Assert $assert) : void
    {
        $this->testFailurePoints('TestTeardown', $assert);
    }

    <<Test>>
    public function invalidSuiteSetupMethods(Assert $assert) : void
    {
        $this->testFailurePoints('SuiteSetup', $assert);
    }

    <<Test>>
    public function invalidSuiteTeardownMethods(Assert $assert) : void
    {
        $this->testFailurePoints('SuiteTeardown', $assert);
    }

    <<Test>>
    public function testSuiteConstructorParams(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('ConstructorParams.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(1);

        $event = $this->malformedEvents->at(0);
        $line = $event->line();
        $method = $event->method();
        $class = $event->className();
        $file = $event->fileName();

        $assert->mixed($line)->isInt();
        invariant(is_int($line), '');
        $assert->mixed($method)->isString();
        invariant(is_string($method), '');
        $assert->mixed($class)->isString();
        invariant(is_string($class), '');
        $assert->mixed($file)->isString();
        invariant(is_string($file), '');

        $assert->int($line)->eq(8);
        $assert->string($method)->is('__construct');
        $assert->string($class)->is($this->suiteName('ConstructorParams'));
        $assert->string($file)->is($this->suitePath('ConstructorParams.php'));
    }

    <<Test>>
    public function emptySuite(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('MissingTestAnnotation.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(1);

        $event = $this->malformedEvents->at(0);
        $line = $event->line();
        $method = $event->method();
        $class = $event->className();
        $file = $event->fileName();

        $assert->mixed($line)->isInt();
        invariant(is_int($line), '');
        $assert->mixed($method)->isNull();
        $assert->mixed($class)->isString();
        invariant(is_string($class), '');
        $assert->mixed($file)->isString();
        invariant(is_string($file), '');

        $assert->int($line)->eq(7);
        $assert->string($class)->is($this->suiteName('MissingTestAnnotation'));
        $assert->string($file)->is($this->suitePath('MissingTestAnnotation.php'));
    }
}
