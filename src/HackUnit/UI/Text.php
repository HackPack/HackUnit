<?hh //strict
namespace HackUnit\UI;

use HackUnit\Core\TestResult;
use HackUnit\Error\Origin;

class Text
{
    protected Vector<Origin> $failures;

    public function __construct(protected TestResult $result)
    {
        $this->failures = $result->getFailures();
    }

    public function getFooter(): string
    {
        return sprintf(
            '%d run, %d failed',
            $this->result->getTestCount(),
            count($this->result->getFailures())
        );
    }
}
