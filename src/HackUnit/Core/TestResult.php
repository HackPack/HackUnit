<?hh //strict
namespace HackUnit\Core;

use HackUnit\Error\TraceParser;
use HackUnit\Error\Origin;

class TestResult
{
    protected Vector<Origin> $failures;

    public function __construct(protected int $runCount = 0, protected int $errorCount = 0)
    {
        $this->failures = Vector {};
    }

    public function testStarted(): void
    {
        $this->runCount++;
    }

    public function testFailed(\Exception $exception): void
    {
        $parser = new TraceParser($exception);
        $this->failures->add($parser->getOrigin());
        $this->errorCount++;
    }

    public function getSummary(): string
    {
        return sprintf("%d run, %d failed", $this->runCount, $this->errorCount);
    }

    public function getFailures(): Vector<Origin>
    {
        return $this->failures;
    }
}
