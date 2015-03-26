<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Error\Origin;
use kilahm\Clio\BackgroundColor;
use kilahm\Clio\Clio;
use kilahm\Clio\Format\Style;
use kilahm\Clio\Format\StyleGroup;
use kilahm\Clio\Format\Text as cText;
use kilahm\Clio\TextColor;

class TextReporter implements ReporterInterface
{
    const string VERSION = "0.4.0-dev";
    private bool $colorIsEnabled = false;

    public static StyleGroup $stylePass = shape(
        'fg' => TextColor::green,
        'bg' => BackgroundColor::normal,
        'effects' => Vector{}
    );

    public static StyleGroup $styleFail = shape(
        'fg' => TextColor::white,
        'bg' => BackgroundColor::red,
        'effects' => Vector{},
    );

    public static StyleGroup $styleSkip = shape(
        'fg' => TextColor::black,
        'bg' => BackgroundColor::light_yellow,
        'effects' => Vector{}
    );

    public static StyleGroup $styleInfo = shape(
        'fg' => TextColor::blue,
        'bg' => BackgroundColor::normal,
        'effects' => Vector{}
    );

    public function __construct(protected Clio $clio)
    {
    }

    public function showInfo(): void
    {
        echo PHP_EOL;
        $this->clio->line(sprintf('HackUnit %s by HackPack.', static::VERSION));
    }

    public function showFailure(...): void
    {
        if($this->colorIsEnabled) {
            $out = $this->clio->style('F')->with(self::$styleFail);
        } else {
            $out = 'F';
        }
        $this->clio->show($out);
    }

    public function showSuccess(...): void
    {
        if($this->colorIsEnabled) {
            $out = $this->clio->style('.')->with(self::$stylePass);
        } else {
            $out = '.';
        }
        $this->clio->show($out);
    }

    public function showSkipped(...): void
    {
        if($this->colorIsEnabled) {
            $out = $this->clio->style('S')->with(self::$styleSkip);
        } else {
            $out = 'S';
        }
        $this->clio->show($out);
    }

    public function showReport(TestResult $result): void
    {
        $this->clio->show($this->getReport($result));
    }

    public function getReport(TestResult $result): string
    {
        return sprintf(
            "%s%s%s\n\n",
            $this->getHeader($result),
            $this->getFailures($result),
            $this->getFooter($result)
        );
    }

    public function getFailureHead(int $failCount): string
    {
        $head = sprintf(
            'There %s %d %s:',
            $failCount > 1 ? 'were' : 'was',
            $failCount,
            $failCount > 1 ? 'failures' : 'failure'
        );
        if($this->colorIsEnabled) {
            $head = $this->clio->style($head)->with(self::$styleFail);
        }
        return $head;
    }

    public function getFailures(TestResult $result): string
    {
        $failCount = $result->failCount();
        if($failCount === 0) {
            return '';
        }

        $failures = '';
        $origins = $result->getFailures();
        foreach($result->getFailures() as $i => $origin) {
            $failures .= $this->banner($i + 1) .
                $origin['test method'] . 'failed at line ' . $origin['test location']['line'] . PHP_EOL .
                $origin['message'] . PHP_EOL;
        }
        return $this->getFailureHead($failCount) . PHP_EOL . PHP_EOL . $failures;
    }

    protected function banner(int $failNumber) : string
    {
        // TODO: use clio to format this
        return str_pad(' Failure ' . $failNumber . ' ', $this->screenWidth(), '-', STR_PAD_BOTH) . PHP_EOL;
    }

    <<__Memoize>>
    protected function screenWidth() : int
    {
        // TODO: use clio to get the screen width
        return (int)exec('tput cols');
    }

    public function getHeader(TestResult $result): string
    {
        $time = $result->getTime();
        if (is_null($time)) return '';

        $unit = ($time == 1) ? 'second' : 'seconds';

        return sprintf("\nTime: %4.2f %s\n\n", $time, $unit);
    }

    public function getFooter(TestResult $result): string
    {
        $numbers = Vector{};
        $testCount = $result->testCount();
        $testWord = $testCount === 1 ? 'test' : 'tests';
        $numbers->add(sprintf("%d %s run", $testCount, $testWord));

        $skipCount = $result->skipCount();
        if ($skipCount > 0) {
            $numbers->add(sprintf("%d skipped", $skipCount));
        }

        $failCount = $result->failCount();
        $skipCount = $result->skipCount();
        if($this->colorIsEnabled) {
            $foot = $failCount > 0 ?
                $this->clio->style('FAIL')->with(self::$styleFail) :
                (
                    $skipCount > 0 ?
                    $this->clio->style('CONDITIONAL PASS')->with(self::$styleSkip) :
                    $this->clio->style('PASS')->with(self::$stylePass)
                );
        } else {
            $foot = $failCount > 0 ? 'FAIL' : ($skipCount > 0 ? 'CONDITIONAL PASS' : 'PASS');
        }

        if($failCount > 0) {
            $numbers->add(sprintf("%d failed", $failCount));
        }

        return $foot . PHP_EOL . implode(', ', $numbers);
    }

    public function enableColor(): void
    {
        $this->colorIsEnabled = true;
    }
}
