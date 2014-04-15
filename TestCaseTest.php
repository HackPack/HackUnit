<?hh //strict
require_once 'WasRun.php';
class TestCaseTest extends TestCase
{
    private ?WasRun $test;

    <<Override>> public function setUp(): void
    {
        $this->test = new WasRun('testMethod');
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
}
