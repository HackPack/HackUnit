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

    public function test_getFooter_should_return_OK_message_when_no_failures(): void
    {
        $result = new TestResult();
        $result->testStarted();
        $ui = new Text($result);
        $this->expect($ui->getFooter())->toEqual("OK 1 run, 0 failed\n");
    }

    public function test_getFailures_should_print_failure_information(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $expected = $this->getExpectedFailures(27, "test_getFailures_should_print_failure_information");
        $this->expect($ui->getFailures())->toEqual($expected);
    }

    public function test_getReport_should_return_entire_message(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $expectedFailures = $this->getExpectedFailures(35, "test_getReport_should_return_entire_message");
        $expected = "\nThere was 1 failure:\n\n" . $expectedFailures . "1 run, 1 failed\n";
        $this->expect($ui->getReport())->toEqual($expected);
    }

    public function test_getFooter_should_include_color_on_OK_statement_when_color_enabled(): void
    {
        $result = new TestResult();
        $result->testStarted();
        $ui = new Text($result);
        $ui->enableColor();

        $expected = sprintf(
            "\033[%d;%dmOK 1 run, 0 failed\033[0m\n",
            $ui->colors->get('bg-green'),
            $ui->colors->get('fg-black')
        );
        $this->expect($ui->getFooter())->toEqual($expected);
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
