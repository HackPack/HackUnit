<?hh //strict
namespace HackUnit\Core;

class TestResult
{
    public function __construct(protected int $runCount = 0, protected int $errorCount = 0)
    {
    }

    public function testStarted(): void
    {
        $this->runCount++;
    }

    public function testFailed(): void
    {
        $this->errorCount++;
    }

    public function getSummary(): string
    {
        return sprintf("%d run, %d failed", $this->runCount, $this->errorCount);
    }
}
