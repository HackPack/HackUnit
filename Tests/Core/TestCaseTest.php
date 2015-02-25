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
        $test = new WasRun(new ReflectionMethod(WasRun::class, 'testMethod'));
        $result = new TestResult();
        $test->run($result);
        $this->expect($result->getTestCount())->toEqual(1);
    }

    <<test>>
    public function testTemplateMethod(): void
    {
        $test = new WasRun(new ReflectionMethod(WasRun::class, 'testMethod'));
        $result = new TestResult();
        $test->run($result);
        $this->expect($test->log)->toEqual(Vector{'testMethod'});
    }

    <<test>>
    public function testFailedResult(): void
    {
        $test = new WasRun(new ReflectionMethod(WasRun::class, 'testBrokenMethod'));
        $result = new TestResult();
        $test->run($result);
        $count = $result->getTestCount();
        $failures = $result->getFailures();
        $this->expect($count)->toEqual(1);
        $this->expect(count($failures))->toEqual(1);
    }

    <<test>>
    public function testSkippedResult(): void
    {
        $test = new WasRun(new ReflectionMethod(WasRun::class, 'testSkip'));
        $result = new TestResult();
        $test->run($result);
        $count = $result->getTestCount();
        $skipped = $result->getSkipped();
        $this->expect($count)->toEqual(1);
        $this->expect(count($skipped))->toEqual(1);
    }

    <<test>>
    public function testSuite(): void
    {
        $firstTest = new WasRun(new ReflectionMethod(WasRun::class, 'testMethod'));
        $secondTest = new WasRun(new ReflectionMethod(WasRun::class, 'testBrokenMethod'));
        $startend = new WasRun(new ReflectionMethod(WasRun::class, 'markAsMalformed'));

        $group = shape(
            'start' => Vector{
                inst_meth($startend, 'load')
            },
            'setup' => Vector{
                new ReflectionMethod(WasRun::class, 'setUp')
            },
            'test' => Vector{$firstTest, $secondTest},
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
        $count = $result->getTestCount();
        $failures = $result->getFailures();
        $this->expect($count)->toEqual(2);
        $this->expect(count($failures))->toEqual(1);

        $this->expect($firstTest->log)->toEqual(Vector{'setUp', 'testMethod', 'tearDown'});
        $this->expect($secondTest->log)->toEqual(Vector{'setUp', 'brokenMethod', 'tearDown'});
    }
}
