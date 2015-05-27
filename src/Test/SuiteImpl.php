<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\SkipListener;
use HackPack\HackUnit\Util\Trace;

class SuiteImpl implements Suite
{
    private Vector<TestCase> $cases = Vector{};
    private Vector<(function():void)> $suiteSetup = Vector{};
    private Vector<(function():void)> $testSetup = Vector{};
    private Vector<(function():void)> $testTeardown = Vector{};
    private Vector<(function():void)> $suiteTeardown = Vector{};
    private Vector<SkipListener> $skipListeners = Vector{};

    public function __construct(
        private string $file,
        private string $className,
        private bool $skip,
    )
    {
    }

    public function fileName() : string
    {
        return $this->file;
    }

    public function className() : string
    {
        return $this->className;
    }

    public function registerSuiteTeardown((function():void) $f) : void
    {
        $this->suiteTeardown->add($f);
    }

    public function registerTestTeardown((function():void) $f) : void
    {
        $this->testTeardown->add($f);
    }

    public function registerSuiteSetup((function():void) $f) : void
    {
        $this->suiteSetup->add($f);
    }

    public function registerTestSetup((function():void) $f) : void
    {
        $this->testSetup->add($f);
    }

    public function registerSkipHandlers(Traversable<(function(Skip):void)> $handlers) : void
    {
        $this->skipListeners->addAll($handlers);
    }

    public function registerTest(
        (function(AssertionBuilder):void) $test,
        \ReflectionMethod $testMethod,
        bool $skip
    ) : void
    {
        $this->cases->add(new TestCase(
            inst_meth($this, 'testSetup'),
            $test,
            inst_meth($this, 'testTeardown'),
            $skip || $this->skip,
            $this->skipEventBuilderFactory($testMethod),
        ));
    }

    private function skipEventBuilderFactory(\ReflectionMethod $testMethod) : (function():Skip)
    {
        return () ==> {
            $fname = $testMethod->getFileName();
            $line = $testMethod->getStartLine();
            return new Skip(Trace::buildItem([
                'file' => is_string($fname) ? $fname : null,
                'function' => $testMethod->name,
                'class' => $testMethod->class,
                'line' => is_int($line) ? $line : null,
            ]));
        };
    }

    public function setup() : void
    {
        foreach($this->suiteSetup as $f) {
            $f();
        }
    }

    public function teardown() : void
    {
        foreach($this->suiteTeardown as $f) {
            $f();
        }
    }

    public function testSetup() : void
    {
        foreach($this->testSetup as $f) {
            $f();
        }
    }

    public function testTeardown() : void
    {
        foreach($this->testTeardown as $f) {
            $f();
        }
    }

    public function cases() : Vector<TestCase>
    {
        return $this->cases;
    }
}
