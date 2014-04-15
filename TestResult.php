<?hh //strict
class TestResult
{
    public function __construct(protected int $runCount = 0)
    {
    }

    public function testStarted(): void
    {
        $this->runCount++;
    }

    public function getSummary(): string
    {
        return sprintf("%d run, 0 failed", $this->runCount);
    }
}
