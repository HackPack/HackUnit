<?hh //strict
require_once 'WasRun.php';
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
            $result = $test->run();
            $expected = "1 run, 0 failed";
            $actual = $result->getSummary();
            if ($expected != $actual) {
                throw new Exception("Expected $expected, got $actual");
            }
        }
    }

    public function testTemplateMethod(): void
    {
        $test = $this->test;
        if ($test) {
            $test->run();
            $expected = 'setUp testMethod tearDown ';
            $actual = $test->log;
            if ($expected != $actual) {
                throw new Exception("Expected $expected, got $actual");
            }
        }
    }

    public function testFailedResult(): void
    {
        $test = new WasRun('testBrokenMethod');
        $result = $test->run();
        $expected = '1 run, 1 failed';
        $actual  = $result->getSummary();
        if ($expected != $actual) {
            throw new Exception("Expected $expected, got $actual");
        }
    }
}
