<?hh //strict
namespace HackUnit\Core;

class TestCaseTest extends TestCase
{
    private ?WasRun $test;

    <<Override>> public function setUp(): void
    {
        $this->test = new WasRun('testMethod');
    }

    public function testResult(): void
    {
        $test = $this->test;
        if ($test) {
            $result = $test->run(new TestResult());
            $this->expect($result->getSummary())->toEqual('1 run, 0 failed');
        }
    }

    public function testTemplateMethod(): void
    {
        $test = $this->test;
        if ($test) {
            $test->run(new TestResult());
            $this->expect($test->log)->toEqual('setUp testMethod tearDown ');
        }
    }

    public function testFailedResult(): void
    {
        $test = new WasRun('testBrokenMethod');
        $result = $test->run(new TestResult());
        $this->expect($result->getSummary())->toEqual('1 run, 1 failed');
    }

    public function testFailedResultFormatting(): void
    {
        $result = new TestResult();
        $result->testStarted();
        try {
            throw new \Exception("Failure!");
        } catch (\Exception $e) {
            $result->testFailed($e);
        }
        $this->expect($result->getSummary())->toEqual('1 run, 1 failed');
    }

    public function testSuite(): void
    {
        $result = new TestResult();
        $suite = new TestSuite();
        $suite->add(new WasRun('testMethod'));
        $suite->add(new WasRun('testBrokenMethod'));
        $result = $suite->run($result);
        $this->expect($result->getSummary())->toEqual('2 run, 1 failed');
    }
}
