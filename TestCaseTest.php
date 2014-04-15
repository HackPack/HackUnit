<?hh //strict
require_once 'WasRun.php';
class TestCaseTest extends TestCase
{
    private ?WasRun $test;

    <<Override>> public function setUp(): void
    {
        $this->test = new WasRun('testMethod');
    }

    public function testRunning(): void
    {
        $test = $this->test;
        if ($test != null) {
            $test->run();
            if (!$test->wasRun) {
                throw new Exception("Expected true, got false");
            }

        }
    }

    public function testSetUp(): void
    {
        $test = $this->test;
        if ($test) {
            $test->run();
            if (!$test->wasSetUp) {
                throw new Exception("Expected true, got false");
            }
        }
    }
}
