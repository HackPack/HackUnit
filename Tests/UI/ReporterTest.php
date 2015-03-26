<?hh // strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Core\ExpectationException;
use HackPack\HackUnit\UI\TextReporter;
use kilahm\Clio\Clio;
use kilahm\Clio\Input\StreamReader;
use kilahm\Clio\Output\StreamWriter;

class ReporterTest extends TestCase
{
    private int $exceptionLine = 0;

    <<test>>
    public function footer_should_have_count_summary_with_skipped(): void
    {
        $result = $this->makeResult(0, 1);
        $ui = $this->makeTextReporter();
        $this->expect($ui->getFooter($result))->toEqual("CONDITIONAL PASS\n1 test run, 1 skipped");
    }

    <<test>>
    public function footer_should_return_count_summary_with_failure(): void
    {
        $result = $this->makeResult(1, 0);
        $ui = $this->makeTextReporter();
        $this->expect($ui->getFooter($result))->toEqual("FAIL\n1 test run, 1 failed");
    }

    <<test>>
    public function footer_should_return_count_summary_with_failure_and_skipped(): void
    {
        $result = $this->makeResult(1, 1);
        $ui = $this->makeTextReporter();
        $this->expect($ui->getFooter($result))->toEqual("FAIL\n2 tests run, 1 skipped, 1 failed");
    }

    <<test>>
    public function footer_should_return_PASS_message_when_no_failures(): void
    {
        $result = new TestResult();
        $result->testStarted();
        $ui = $this->makeTextReporter();
        $this->expect($ui->getFooter($result))->toEqual("PASS\n1 test run");
    }

    <<test>>
    public function getFailures_should_have_failure_information(): void
    {
        $ui = $this->makeTextReporter();
        $result = $this->makeResult(2, 0);
        $expected = 'There were 2 failures:' . PHP_EOL . PHP_EOL;
        $expected .= $this->getExpectedFailures(__LINE__ + 1, 2, __METHOD__);
        $this->expect($ui->getFailures($result))->toEqual($expected);
    }

    <<test>>
    public function getReport_should_return_entire_message(): void
    {
        $ui = $this->makeTextReporter();
        $result = $this->makeResult(2, 0);
        $result->startTimer();
        $result->stopTimer();
        $expectedFailures = $this->getExpectedFailures(__LINE__ + 3, 2, __METHOD__);
        $time = sprintf('%4.2f', $result->getTime());
        $expected = "\nTime: $time seconds\n\nThere were 2 failures:\n\n" . $expectedFailures . "FAIL\n1 run, 1 failed\n\n";
        $this->expect($ui->getReport($result))->toEqual($expected);
    }

    <<test>>
    public function getFooter_should_include_color_on_PASS_statement_when_color_enabled(): void
    {
        $result = new TestResult();
        $result->testStarted();
        $ui = $this->makeTextReporter();
        $ui->enableColor();
        $style = TextReporter::$stylePass;
        $expected = \kilahm\Clio\Format\Text::style('PASS')->with($style) . PHP_EOL .
            '1 test run';
        $this->expect($ui->getFooter($result))->toEqual($expected);
    }

    <<test>>
    public function footer_should_include_color_for_skips_when_color_enabled(): void
    {
        $result = $this->makeResult(0, 1);
        $ui = $this->makeTextReporter();
        $ui->enableColor();
        $style = TextReporter::$styleSkip;
        $expected = \kilahm\Clio\Format\Text::style('CONDITIONAL PASS')->with($style) . PHP_EOL .
            '1 test run, 1 skipped';
        $this->expect($ui->getFooter($result))->toEqual($expected);
    }

    <<test>>
    public function footer_should_include_color_for_FAILURES_when_color_enabled(): void
    {
        $result = $this->makeResult(1, 0);
        $ui = $this->makeTextReporter();
        $ui->enableColor();
        $style = TextReporter::$styleFail;
        $expected = \kilahm\Clio\Format\Text::style('FAIL')->with($style) . PHP_EOL .
            '1 test run, 1 failed';
        $this->expect($ui->getFooter($result))->toEqual($expected);
    }

    <<test>>
    public function header_includes_time_from_result(): void
    {
        $result = new TestResult();
        $result->startTimer();
        $ui = $this->makeTextReporter();
        $this->expect($ui->getHeader($result))->toMatch('/Time: [0-9]+([.][0-9]{1,2})? seconds?/');
    }

    private function makeTextReporter() : TextReporter
    {
        return new TextReporter(self::makeClio());
    }

    <<__Memoize>>
    private static function makeClio() : Clio
    {
        $reader = new StreamReader(fopen('/dev/null', 'r'));
        $writer = new StreamWriter(fopen('/dev/null', 'w'));
        return new Clio(
            'test',
            Vector{},
            $reader,
            $writer,
            new \kilahm\Clio\Util\Parser(Vector{})
        );
    }

    protected function getExpectedFailures(int $testLine, int $failCount, string $method): string
    {
        $class = __CLASS__;
        $file = __FILE__;
        $expected = '';
        for($i = 0; $i < $failCount; $i++) {
            $expected .= $this->banner($i+1);
            $expected .= <<<EOT
{$method} failed on line {$testLine}
Original exception thrown on {$file}:{$this->exceptionLine}

Failure {$i}


EOT;
        }
        return $expected;
    }

    protected function banner(int $failNumber) : string
    {
        return str_pad(' Failure ' . $failNumber . ' ', $this->screenWidth(), '-', STR_PAD_BOTH) . PHP_EOL;
    }

    <<__Memoize>>
    protected function screenWidth() : int
    {
        return (int)exec('tput cols');
    }

    protected function makeResult(int $failCount, int $skipCount): TestResult
    {
        $result = new TestResult();

        $this->exceptionLine = __LINE__ + 4;
        for($i = 0; $i < $failCount; $i++) {
            $result->testStarted();
            try{
                throw new \Exception('Failure ' . $i);
            } catch (\Exception $e) {
                $result->testFailed($e);
            }
        }

        for($i = 0; $i < $skipCount; $i++) {
            $result->testStarted();
            $result->testSkipped();
        }

        return $result;
    }
}
