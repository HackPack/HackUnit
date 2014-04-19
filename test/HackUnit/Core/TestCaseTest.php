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
            $expected = "1 run, 0 failed";
            $actual = $result->getSummary();
            if ($expected != $actual) {
                throw new \Exception("Expected $expected, got $actual");
            }
        }
    }

    public function testTemplateMethod(): void
    {
        $test = $this->test;
        if ($test) {
            $test->run(new TestResult());
            $expected = 'setUp testMethod tearDown ';
            $actual = $test->log;
            if ($expected != $actual) {
                throw new \Exception("Expected $expected, got $actual");
            }
        }
    }

    public function testFailedResult(): void
    {
        $test = new WasRun('testBrokenMethod');
        $result = $test->run(new TestResult());
        $expected = '1 run, 1 failed';
        $actual  = $result->getSummary();
        if ($expected != $actual) {
            throw new \Exception("Expected $expected, got $actual");
        }
    }

    public function testFailedResultFormatting(): void
    {
        $result = new TestResult();
        $result->testStarted();
        $result->testFailed();
        $expected = '1 run, 1 failed';
        $actual = $result->getSummary();
        if ($expected != $actual) {
            throw new \Exception("Expected $expected, got $actual");
        }
    }

    public function testSuite(): void
    {
        $result = new TestResult();
        $suite = new TestSuite();
        $suite->add(new WasRun('testMethod'));
        $suite->add(new WasRun('testBrokenMethod'));
        $result = $suite->run($result);
        $actual = $result->getSummary();
        $expected = '2 run, 1 failed';
        if ($expected != $actual) {
            throw new \Exception("Expected $expected, got $actual");
        }
    }
}
