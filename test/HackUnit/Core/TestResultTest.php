<?hh //strict
namespace HackUnit\Core;

class TestResultTest extends TestCase
{
    public function test_getFailures_returns_method(): void
    {
        $result = $this->getResult();
        $failures = $result->getFailures();
        $failure = $failures->at(0);
        $method = $failure['method'];
        $this->expect($method)->toEqual('HackUnit\Core\TestResultTest::test_getFailures_returns_method');
    }

    public function test_getFailures_returns_message(): void
    {
        $result = $this->getResult();
        $failures = $result->getFailures();
        $failure = $failures->at(0);
        $message = $failure['message'];
        $this->expect($message)->toEqual('Failure');
    }

    public function test_getFailures_returns_location(): void
    {
        $result = $this->getResult();
        $failures = $result->getFailures();
        $failure = $failures->at(0);
        $location = $failure['location'];
        $this->expect($location)->toEqual(__FILE__ . ':26');
    }

    protected function getResult(): TestResult
    {
        $result = new TestResult();
        try {
            throw new \Exception("Failure");
        } catch (\Exception $e) {
            $result->testFailed($e);
        }
        return $result;
    }
}
