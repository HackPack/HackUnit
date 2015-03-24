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

    private static StyleGroup $stylePass = shape(
        'fg' => TextColor::green,
        'bg' => BackgroundColor::normal,
        'effects' => Vector{}
    );

    private static StyleGroup $styleFail = shape(
        'fg' => TextColor::white,
        'bg' => BackgroundColor::red,
        'effects' => Vector{},
    );

    private static StyleGroup $styleSkip = shape(
        'fg' => TextColor::black,
        'bg' => BackgroundColor::light_yellow,
        'effects' => Vector{}
    );

    private static StyleGroup $styleInfo = shape(
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
        $this->clio->show(
            $this->clio->style('F')->with(self::$styleFail)
        );
    }

    public function showSuccess(...): void
    {
        $this->clio->show(
            $this->clio->style('.')->with(self::$stylePass)
        );
    }

    public function showSkipped(...): void
    {
        $this->clio->show(
            $this->clio->style('S')->with(self::$styleSkip)
        );
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
        return sprintf(
            "There %s %d %s:\n\n",
            $failCount > 1 ? 'were' : 'was',
            $failCount,
            $failCount > 1 ? 'failures' : 'failure'
        );
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
            $method = sprintf("%d) %s\n", $i + 1, $origin['method']);
            $message = sprintf("%s\n", $origin['message']);
            $location = sprintf("%s\n\n", $origin['location']);
            $failures .= $method . $message . $location;
        }
        return $this->getFailureHead($failCount) . PHP_EOL . PHP_EOL . $failures;
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
        $numbers->add(sprintf("%d tests run", $result->testCount()));

        $skipCount = $result->skipCount();
        if ($skipCount > 0) {
            $numbers->add(sprintf("%d skipped", $skipCount));
        }

        $failCount = $result->failCount();
        $foot = $failCount > 0 ?
            $this->clio->style('FAILURES')->with(self::$styleFail) :
            $this->clio->style('PASS')->with(self::$stylePass);

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
