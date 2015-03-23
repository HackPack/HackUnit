<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Error\Origin;
use kilahm\Clio\Clio;
use kilahm\Clio\TextColor;
use kilahm\Clio\Format\Text as cText;

class TextReporter implements ReporterInterface
{

    protected int $maxColumns = 63;

    protected int $currentColumn = 0;

    public function __construct(protected Clio $clio)
    {
    }

    public function getReport(TestResult $result): string
    {
        $failCount = count($result->getFailures());
        return sprintf(
            "%s%s%s%s",
            $this->getHeader($result) ?: "\n",
            $failCount == 0 ? '' : sprintf(
                "There %s %d %s:\n\n",
                $failCount > 1 ? 'were' : 'was',
                $failCount,
                $failCount > 1 ? 'failures' : 'failure'
            ),
            $this->getFailures($result),
            $this->getFooter($result)
        );
    }

    public function getFailures(TestResult $result): string
    {
        $failures = "";
        $origins = $result->getFailures();
        for($i = 0; $i < $origins->count(); $i++) {
            $method = sprintf("%d) %s\n", $i + 1, $origins[$i]['method']);
            $message = sprintf("%s\n\n", $origins[$i]['message']);
            $location = sprintf("%s\n\n", $origins[$i]['location']);
            $failures .= $method . $message . $location;
        }
        return $failures;
    }

    public function getHeader(TestResult $result): string
    {
        $time = $result->getTime();
        if (is_null($time)) return '';

        $unit = ($time == 1) ? 'second' : 'seconds';

        return sprintf("\n\nTime: %4.2f %s\n\n", $time, $unit);
    }

    public function getFooter(TestResult $result): string
    {
        $message = $this->footerBuilder($result);
        return $this->formatFooter($message, count($result->getFailures()) > 0);
    }

    private function footerBuilder(TestResult $result): string
    {
        $output = "";
        $failureCount = count($result->getFailures());
        $skippedCount = count($result->getSkipped());
        if ($failureCount > 0 ) {
            $output .= "FAILURES!\n";
        } else {
            $output .= "OK ";
        }

        $output .= sprintf("%d run, ", $result->getTestCount());

        if ($skippedCount > 0) {
            $output .= sprintf("%d skipped, ", $skippedCount);
        }

        $output .= sprintf("%d failed", $failureCount);

        return $output;
    }

    public function enableColor(): void
    {
        $this->colorIsEnabled = true;
    }

    protected function formatFooter(string $footer, bool $hasFailures): string
    {
        $lines = Vector::fromItems(explode(PHP_EOL, $footer));
        $width = max($lines->map($line ==> strlen($line)));
        return implode(PHP_EOL, $lines->map($line ==> {
            $text = cText::style($line)->toWidth($width)->left();
            if($hasFailures) {
                $text->fg(TextColor::white)->bg(TextColor::red);
            } else {
                $text->fg(TextColor::black)->bg(TextColor::green);
            }
            $text->render();
        })) . PHP_EOL;
    }
}
