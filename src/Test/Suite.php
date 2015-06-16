<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\TestCase;
use HackPack\HackUnit\Util\Trace;

class Suite implements \HackPack\HackUnit\Contract\Test\Suite
{
    private Vector<(function():void)> $suiteup = Vector{};
    private Vector<(function():void)> $suitedown = Vector{};
    private Vector<(function():void)> $testup = Vector{};
    private Vector<(function():void)> $testdown = Vector{};
    private Vector<\ReflectionMethod> $testMethods = Vector{};
    private bool $skip;
    private mixed $instance;

    public function __construct(
        \ReflectionClass $mirror,
        private (function(
            (function(Assert):void),
            Vector<(function():void)>,
            Vector<(function():void)>,
        ) : TestCase) $caseBuilder,
    )
    {
        $this->skip = $mirror->getAttribute('Skip') !== null;
        $this->instance = $mirror->newInstance();
    }

    public function registerSuiteSetup(\ReflectionMethod $method) : void
    {
        $this->suiteup->add(() ==> {$method->invoke($this->instance);});
    }

    public function registerSuiteTeardown(\ReflectionMethod $method) : void
    {
        $this->suitedown->add(() ==> {$method->invoke($this->instance);});
    }

    public function registerTestSetup(\ReflectionMethod $method) : void
    {
        $this->testup->add(() ==> {$method->invoke($this->instance);});
    }

    public function registerTestTeardown(\ReflectionMethod $method) : void
    {
        $this->testdown->add(() ==> {$method->invoke($this->instance);});
    }

    public function registerTestMethod(\ReflectionMethod $testMethod) : void
    {
        $this->testMethods->add($testMethod);
    }

    public function setup() : void
    {
        foreach($this->suiteup as $f) {
            $f();
        }
    }

    public function teardown() : void
    {
        foreach($this->suitedown as $f) {
            $f();
        }
    }

    public function testCases() : Vector<TestCase>
    {
        return $this->testMethods->map($m ==> {
            if($this->skip) {
                $test = $this->buildSkipTest($m, 'Suite marked skip.');
            } elseif($m->getAttribute('Skip') !== null) {
                $test = $this->buildSkipTest($m, 'Test marked skip.');
            } else {
                $test = (Assert $a) ==> {
                    $m->invoke($this->instance, $a);
                };
            }
            $builder = $this->caseBuilder;
            return $builder($test, $this->testup, $this->testdown);
        });
    }

    private function buildSkipTest(\ReflectionMethod $m, string $reason) : (function(Assert):void)
    {
        return (Assert $assert) ==> {
            $assert->skip(
                $reason,
                Trace::buildItem([
                    'file' => $m->getFileName(),
                    'line' => $m->getStartLine(),
                    'function' => $m->getName(),
                    'class' => $m->getDeclaringClass()->getName(),
                ]),
            );
        };
    }
}
