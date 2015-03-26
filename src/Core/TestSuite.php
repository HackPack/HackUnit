<?hh //strict
namespace HackPack\HackUnit\Core;

use ReflectionMethod;
use HackPack\HackUnit\Exception\MarkTestAsSkipped;

type TestInstance = shape(
    'instance' => TestCase,
    'method' => ReflectionMethod,
);

type TestGroup = shape(
    'start' => Vector<(function():void)>,
    'setup' => Vector<ReflectionMethod>,
    'tests' => Vector<TestInstance>,
    'teardown' => Vector<ReflectionMethod>,
    'end' => Vector<(function():void)>,
);

class TestSuite implements TestInterface
{
    public function __construct(protected Vector<TestGroup> $tests = Vector {})
    {
    }

    public function run(TestResult $result) : void
    {
        array_walk($this->tests->toArray(), $group ==> $this->runGroup($group, $result));
    }

    private function runGroup(TestGroup $group, TestResult $result) : void
    {
        if($group['tests']->count() === 0) {
            return;
        }

        try{
            $result->groupStarted();
            array_walk($group['start']->toArray(), $method ==> $method());
            array_walk($group['tests']->toArray(), (TestInstance $test) ==> $this->runTest($test['instance'], $test['method'], $group, $result));
            array_walk($group['end']->toArray(), $method ==> $method());
        } catch(\Exception $e) {
            $result->groupError($e);
        }

    }

    private function runTest(TestCase $instance, \ReflectionMethod $method, TestGroup $group, TestResult $result) : void
    {
        array_walk($group['setup']->toArray(), $method ==> $method->invoke($instance));
        $instance->run($result, $method);
        array_walk($group['teardown']->toArray(), $method ==> $method->invoke($instance));
    }
}
