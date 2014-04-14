<?hh //strict
require_once 'WasRun.php';
class TestCaseTest extends TestCase
{
    public function testRunning(): void
    {
        $test = new WasRun('testMethod');
        if ($test->wasRun) {
            throw new Exception("Expected false, got true");
        }
        $test->run();
        if (!$test->wasRun) {
            throw new Exception("Expected true, got false");
        }
    }
}
