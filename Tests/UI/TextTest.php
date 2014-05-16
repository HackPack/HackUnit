<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Core\ExpectationException;
use HackPack\HackUnit\UI\Text;

class TextTest extends TestCase
{
    public function test_getFooter_should_return_count_summary(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $this->expect($ui->getFooter())->toEqual("FAILURES!\n1 run, 1 failed\n");
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
        $expected = $this->getExpectedFailures(28, "test_getFailures_should_print_failure_information");
        $this->expect($ui->getFailures())->toEqual($expected);
    }

    public function test_getReport_should_return_entire_message(): void
    {
        $result = $this->getResult();
        $result->startTimer();
        $ui = new Text($result);
        $expectedFailures = $this->getExpectedFailures(36, "test_getReport_should_return_entire_message");
        $expected = "\nTime: 0.00 seconds\n\nThere was 1 failure:\n\n" . $expectedFailures . "FAILURES!\n1 run, 1 failed\n";
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

    public function test_getFooter_should_include_color_and_padding_for_FAILURES_when_color_enabled(): void
    {
        $result = $this->getResult();
        $ui = new Text($result);
        $ui->enableColor();

        $expected = sprintf(
            "\033[%d;%dmFAILURES!      \n1 run, 1 failed\033[0m\n",
            $ui->colors->get('bg-red'),
            $ui->colors->get('fg-white')
        );
        $this->expect($ui->getFooter())->toEqual($expected);
    }

    public function test_getHeader_includes_time_from_result(): void
    {
        $result = $this->getResult();
        $result->startTimer();
        $ui = new Text($result);
        $this->expect($ui->getHeader())->toMatch("/\nTime: [0-9]+([.][0-9]{1,2})? seconds?\n\n/");
    }

    protected function getExpectedFailures(int $line, string $method): string
    {
        $expected  = "1) HackPack\HackUnit\UI\TextTest::$method\n";
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
