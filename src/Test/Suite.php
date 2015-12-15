<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\TestCase;
use HackPack\HackUnit\Util\Trace;

class Suite implements \HackPack\HackUnit\Contract\Test\Suite
{
    private Vector<(function():Awaitable<void>)> $suiteup = Vector{};
    private Vector<(function():Awaitable<void>)> $suitedown = Vector{};
    private Vector<(function():Awaitable<void>)> $testup = Vector{};
    private Vector<(function():Awaitable<void>)> $testdown = Vector{};
    private Vector<\ReflectionMethod> $testMethods = Vector{};
    private bool $skip;
    private mixed $instance;

    public function __construct(
        \ReflectionClass $mirror,
        private (function(
            (function(Assert):Awaitable<void>),
            Vector<(function():Awaitable<void>)>,
            Vector<(function():Awaitable<void>)>,
        ) : TestCase) $caseBuilder,
    )
    {
        $this->skip = $mirror->getAttribute('Skip') !== null;
        $this->instance = $mirror->newInstance();
    }

    public function registerSuiteSetup(\ReflectionMethod $method) : void
    {
        $this->suiteup->add(async () ==> {
            $result = $method->invoke($this->instance);
            if($method->isAsync()) {
                 await $result;
            }
        });
    }

    public function registerSuiteTeardown(\ReflectionMethod $method) : void
    {
        $this->suitedown->add(async () ==> {
            $result = $method->invoke($this->instance);
            if($method->isAsync()) {
                 await $result;
            }
        });
    }

    public function registerTestSetup(\ReflectionMethod $method) : void
    {
        $this->testup->add(async () ==> {
            $result = $method->invoke($this->instance);
            if($method->isAsync()) {
                 await $result;
            }
        });
    }

    public function registerTestTeardown(\ReflectionMethod $method) : void
    {
        $this->testdown->add(async () ==> {
            $result = $method->invoke($this->instance);
            if($method->isAsync()) {
                 await $result;
            }
        });
    }

    public function registerTestMethod(\ReflectionMethod $testMethod) : void
    {
        $this->testMethods->add($testMethod);
    }

    public async function setup() : Awaitable<void>
    {
        await \HH\Asio\v($this->suiteup->map($f ==> $f()));
    }

    public async function teardown() : Awaitable<void>
    {
        await \HH\Asio\v($this->suitedown->map($f ==> $f()));
    }

    public function testCases() : Vector<TestCase>
    {
        return $this->testMethods->map($m ==> {
            if($this->skip) {
                $test = $this->buildSkipTest($m, 'Suite marked skip.');
            } elseif($m->getAttribute('Skip') !== null) {
                $test = $this->buildSkipTest($m, 'Test marked skip.');
            } else {
                $test = async (Assert $a) ==> {
                    if($m->isAsync()) {
                        await $m->invoke($this->instance, $a);
                    } else {
                        $m->invoke($this->instance, $a);
                    }
                };
            }
            $builder = $this->caseBuilder;
            return $builder($test, $this->testup, $this->testdown);
        });
    }

    private function buildSkipTest(\ReflectionMethod $m, string $reason) : (function(Assert):Awaitable<void>)
    {
        return async (Assert $assert) ==> {
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
