<?hh //strict
namespace HackUnit\UI;

use HackUnit\Core\TestCase;
use HackUnit\Core\TestResult;
use HackUnit\Core\ExpectationException;

class TextTest extends TestCase
{
    public function test_getFooter_should_return_count_summary(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $this->expect($ui->getFooter())->toEqual("1 run, 1 failed\n");
    }

    public function test_getFailures_should_print_failure_information(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $expected = $this->getExpectedFailures(19, "test_getFailures_should_print_failure_information");
        $this->expect($ui->getFailures())->toEqual($expected);
    }

    public function test_getReport_should_return_entire_message(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $expectedFailures = $this->getExpectedFailures(27, "test_getReport_should_return_entire_message");
        $expected = "\n" . $expectedFailures . "1 run, 1 failed\n";
        $this->expect($ui->getReport())->toEqual($expected);
    }

    protected function getExpectedFailures(int $line, string $method): string
    {
        $expected  = "1) HackUnit\UI\TextTest::$method\n";
        $expected .= "Something is wrong\n\n";
        $expected .= __FILE__ . ":$line\n\n";
        return $expected;
    }

    protected function getResult(): TestResult
    {
        $result = new TestResult();
        $result->testStarted();
        try {
            throw new \Exception("Something is wrong");
        } catch (\Exception $e) {
            $result->testFailed($e);
        }
        return $result;
    }
}
