<?hh //strict
namespace HackPack\HackUnit\Tests\Core;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Core\TestSuite;
use ReflectionMethod;

class TestCaseTest extends TestCase
{
    <<test>>
    public function testResult(): void
    {
        $test = new WasRun();
        $result = new TestResult();
        $test->run($result, new ReflectionMethod(WasRun::class, 'testMethod'));
        $this->expect($result->testCount())->toEqual(1);
    }

    <<test>>
    public function testTemplateMethod(): void
    {
        $test = new WasRun();
        $result = new TestResult();
        $test->run($result, new ReflectionMethod(WasRun::class, 'testMethod'));
        $this->expect($test->log)->toEqual(Vector{'testMethod'});
    }

    <<test>>
    public function testFailedResult(): void
    {
        $test = new WasRun();
        $result = new TestResult();
        $test->run($result, new ReflectionMethod(WasRun::class, 'testBrokenMethod'));
        $count = $result->testCount();
        $failures = $result->getFailures();
        $this->expect($count)->toEqual(1);
        $this->expect(count($failures))->toEqual(1);
    }

    <<test>>
    public function skippedResult(): void
    {
        $test = new WasRun();
        $result = new TestResult();
        $test->run($result, new ReflectionMethod(WasRun::class, 'testSkip'));
        $count = $result->testCount();
        $skipped = $result->skipCount();
        $this->expect($count)->toEqual(1);
        $this->expect(count($skipped))->toEqual(1);
    }

    <<test>>
    public function testSuite(): void
    {
        $firstTest = new WasRun();
        $secondTest = new WasRun();
        $startend = new WasRun();

        $group = shape(
            'start' => Vector{
                inst_meth($startend, 'load')
            },
            'setup' => Vector{
                new ReflectionMethod(WasRun::class, 'setUp')
            },
            'tests' => Vector{
                shape(
                    'instance' => $firstTest,
                    'method' => new ReflectionMethod(WasRun::class, 'testMethod')
                ),
                shape(
                    'instance' => $secondTest,
                    'method' => new ReflectionMethod(WasRun::class, 'brokenMethod')
                ),
            },
            'teardown' => Vector{
                new ReflectionMethod(WasRun::class, 'tearDown')
            },
            'end' => Vector{
                inst_meth($startend, 'unload')
            },
        );
        $suite = new TestSuite(Vector{$group});
        $result = new TestResult();
        $suite->run($result);
        $count = $result->testCount();
        $failures = $result->getFailures();
        $this->expect($count)->toEqual(2);
        $this->expect(count($failures))->toEqual(1);

        $this->expect($firstTest->log)->toEqual(Vector{'setUp', 'testMethod', 'tearDown'});
        $this->expect($secondTest->log)->toEqual(Vector{'setUp', 'brokenMethod', 'tearDown'});
    }
}
