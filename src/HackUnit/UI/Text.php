<?hh //strict
namespace HackUnit\UI;

use HackUnit\Core\TestResult;
use HackUnit\Error\Origin;

class Text implements ReporterInterface
{
    protected Vector<Origin> $failures;

    public function __construct(protected TestResult $result)
    {
        $this->failures = $result->getFailures();
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
        return sprintf(
            "%d run, %d failed\n",
            $this->result->getTestCount(),
            count($this->result->getFailures())
        );
    }
}
