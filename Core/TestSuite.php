<?hh //strict
namespace HackPack\HackUnit\Core;

use ReflectionMethod;
use HackPack\HackUnit\Exception\MarkTestAsSkipped;

type TestGroup = shape(
    'start' => (function():void),
    'setup' => (function():void),
    'tests' => Vector<(function():void)>,
    'teardown' => (function():void),
    'end' => (function():void),
);

class TestSuite implements TestInterface
{
    protected Vector<TestGroup> $tests;

    public function __construct(Vector<?TestGroup> $tests = Vector {})
    {
        /* HH_FIXME[4110] */
        $this->tests = $tests->filter($g ==> $g !== null);
    }

    public function addGroup(TestGroup $group): void
    {
        $this->tests->add($group);
    }

    public function run(TestResult $result): TestResult
    {
        foreach ($this->tests as $group) {
            $result->groupStarted();
            $group['start']();
            foreach($group['tests'] as $test) {
                $result->testStarted();
                $group['setup']();
                try {
                    $test();
                    $result->testPassed();
                } catch(MarkTestAsSkipped $e) {
                    $result->testSkipped($e);
                } catch (\Exception $e) {
                    $result->testFailed($e);
                }
                $group['teardown']();
            }
            $group['end'];
        }
        return $result;
    }
}
