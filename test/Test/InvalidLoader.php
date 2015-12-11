<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Loader;
use HackPack\HackUnit\Tests\Mocks\Test\Suite;
use HackPack\HackUnit\Tests\TraceItemTest;
use HackPack\HackUnit\Util\TraceItem;

newtype UpDownFailure = shape(
    'line' => int,
    'method' => string,
    'class' => string,
    'file' => string,
);

class InvalidLoaderTest
{
    use TraceItemTest;

    private Vector<MalformedSuite> $malformedEvents = Vector{};
    private (function(\ReflectionClass):Suite) $suiteBuilder;
    private Loader $loader;
    private string $fixturesPath;

    private Vector<UpDownFailure> $failurePoints = Vector{
        shape(
            'file' => 'Constructor.php',
            'class' => 'Constructor',
            'method' => '__construct',
            'line' => 8,
        ),
        shape(
            'file' => 'Destructor.php',
            'class' => 'Destructor',
            'method' => '__destruct',
            'line' => 8,
        ),
        shape(
            'file' => 'Params.php',
            'class' => 'Params',
            'method' => 'params',
            'line' => 8,
        ),
        shape(
            'file' => 'StaticMethods.php',
            'class' => 'StaticMethods',
            'method' => 'noStatics',
            'line' => 8,
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
        $fullPath = rtrim($this->suitePath($name), '/');

        $this->loader->including($fullPath);
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(4);

        foreach($this->failurePoints as $index => $failure) {
            $event = $this->malformedEvents->at($index);
            $this->checkTrace(
                $event->traceItem(),
                shape(
                    'line' => $failure['line'],
                    'function' => $failure['method'],
                    'class' => $this->suiteName($name) . '\\' . $failure['class'],
                    'file' => $fullPath . '/' . $failure['file'],
                ),
                $assert,
            );
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
        $this->checkTrace(
            $event->traceItem(),
            shape(
                'line' => 7,
                'function' => '__construct',
                'class' => $this->suiteName('ConstructorParams'),
                'file' => $this->suitePath('ConstructorParams.php'),
            ),
            $assert,
        );
    }

    <<Test>>
    public function testAsConstructor(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('Test/Constructor.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(1);

        $this->checkTrace(
            $this->malformedEvents->at(0)->traceItem(),
            shape(
                'line' => 10,
                'function' => '__construct',
                'class' => $this->suiteName('Test\Constructor'),
                'file' => $this->suitePath('Test/Constructor.php')
            ),
            $assert,
        );
    }

    <<Test>>
    public function testAsDestructor(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('Test/Destructor.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(1);

        $this->checkTrace(
            $this->malformedEvents->at(0)->traceItem(),
            shape(
                'line' => 8,
                'function' => '__destruct',
                'class' => $this->suiteName('Test\Destructor'),
                'file' => $this->suitePath('Test/Destructor.php')
            ),
            $assert,
        );
    }

    <<Test>>
    public function testAsStatic(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('Test/StaticMethods.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(0);
        $assert->int($this->malformedEvents->count())->eq(1);

        $this->checkTrace(
            $this->malformedEvents->at(0)->traceItem(),
            shape(
                'line' => 8,
                'function' => 'setItUp',
                'class' => $this->suiteName('Test\StaticMethods'),
                'file' => $this->suitePath('Test/StaticMethods.php')
            ),
            $assert,
        );
    }

    <<Test>>
    public function tooFewParamsForTest(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('Test/TooFewParams.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(1);
        $assert->int($this->malformedEvents->count())->eq(1);

        $this->checkTrace(
            $this->malformedEvents->at(0)->traceItem(),
            shape(
                'line' => 10,
                'function' => 'test',
                'class' => $this->suiteName('Test\TooFewParams'),
                'file' => $this->suitePath('Test/TooFewParams.php')
            ),
            $assert,
        );
    }

    <<Test>>
    public function tooManyParamsForTest(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('Test/TooManyParams.php'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(1);
        $assert->int($this->malformedEvents->count())->eq(1);

        $this->checkTrace(
            $this->malformedEvents->at(0)->traceItem(),
            shape(
                'line' => 8,
                'function' => 'test',
                'class' => $this->suiteName('Test\TooManyParams'),
                'file' => $this->suitePath('Test/TooManyParams.php')
            ),
            $assert,
        );
    }
}
