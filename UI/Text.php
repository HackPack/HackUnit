<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Error\Origin;

class Text implements ReporterInterface
{
    public Map<string, int> $colors = Map {
        'bg-green' => 42,
        'fg-black' => 30,
        'bg-red' => 41,
        'fg-white' => 37
    };

    protected Vector<Origin> $failures;

    protected bool $colorIsEnabled;

    public function __construct(protected TestResult $result)
    {
        $this->failures = $result->getFailures();
        $this->colorIsEnabled = false;
    }

    public function getReport(): string
    {
        $failCount = count($this->result->getFailures());
        return sprintf(
            "%s%s%s%s",
            $this->getHeader() ?: "\n", 
            $failCount == 0 ? '' : sprintf(
                "There %s %d %s:\n\n",
                $failCount > 1 ? 'were' : 'was',
                $failCount,
                $failCount > 1 ? 'failures' : 'failure'
            ),
            $this->getFailures(),
            $this->getFooter()
        );
    }

    public function getFailures(): string
    {
        $failures = "";
        for($i = 0; $i < $this->failures->count(); $i++) {
            $method = sprintf("%d) %s\n", $i + 1, $this->failures[$i]['method']);
            $message = sprintf("%s\n\n", $this->failures[$i]['message']);
            $location = sprintf("%s\n\n", $this->failures[$i]['location']);
            $failures .= $method . $message . $location;
        }
        return $failures;
    }

    public function getHeader(): string
    {
        $time = $this->result->getTime();
        if (is_null($time)) return '';

        $unit = ($time == 1) ? 'second' : 'seconds';

        return sprintf("\nTime: %4.2f %s\n\n", $time, $unit);
    }

    public function getFooter(): string
    {
        $failureCount = count($this->result->getFailures());
        return $this->formatFooter(sprintf(
            "%s%d run, %d failed",
            $failureCount > 0 ? "FAILURES!\n" : 'OK ',
            $this->result->getTestCount(),
            $failureCount
        ), $failureCount > 0);
    }

    public function enableColor(): void
    {
        $this->colorIsEnabled = true;
    }

    protected function formatFooter(string $footer, bool $hasFailures): string
    {
        $formatted = $footer;
        $lines = explode("\n", $formatted);
        $padding = max(array_map(fun('strlen'), $lines));
        if ($this->colorIsEnabled) {
            $message = array_reduce($lines, ($r, $l) ==> $r . str_pad($l, $padding) . "\n", "");
            $formatted = sprintf(
                "\033[%d;%dm%s\033[0m", 
                $this->colors->get($hasFailures ? 'bg-red' : 'bg-green'),
                $this->colors->get($hasFailures ? 'fg-white' : 'fg-black'),
                trim($message)
            );
        }
        return $formatted . "\n";
    }
}
