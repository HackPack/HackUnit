<?hh //strict
namespace HackUnit\UI;

use HackUnit\Core\TestResult;
use HackUnit\Error\Origin;

class Text implements ReporterInterface
{
    public Map<string, int> $colors = Map {
        'bg-green' => 42,
        'fg-black' => 30
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
        return sprintf(
            "\n%s%s",
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

    public function getFooter(): string
    {
        $failureCount = count($this->result->getFailures());
        return $this->formatFooter(sprintf(
            "%s%d run, %d failed",
            $failureCount > 0 ? '' : 'OK ',
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
        if (!$hasFailures && $this->colorIsEnabled) {
            $formatted = sprintf(
                "\033[%d;%dm%s\033[0m", 
                $this->colors->get('bg-green'),
                $this->colors->get('fg-black'),
                $formatted
            );
        }
        return $formatted .= "\n";
    }
}
