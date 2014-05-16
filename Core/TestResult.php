<?hh //strict
namespace HackPack\HackUnit\Core;

use HackPack\HackUnit\Error\TraceParser;
use HackPack\HackUnit\Error\Origin;

class TestResult
{
    protected Vector<Origin> $failures;
    protected ?float $startTime;

    public function __construct(protected int $runCount = 0, protected int $errorCount = 0)
    {
        $this->failures = Vector {};
    }

    public function testStarted(): void
    {
        $this->runCount++;
    }

    public function startTimer(): void
    {
        $this->startTime = microtime(true);
    }

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    public function getTime(): ?float
    {
        $time = null;
        $startTime = $this->startTime;
        if (!is_null($startTime)) {
            $time = microtime(true) - $startTime;
        }
        return $time;
    }

    public function getTestCount(): int
    {
        return $this->runCount;
    }

    public function testFailed(\Exception $exception): void
    {
        $parser = new TraceParser($exception);
        $this->failures->add($parser->getOrigin());
        $this->errorCount++;
    }

    public function getExitCode(): int
    {
        return ($this->failures->count() > 0)
            ? 1 : 0;
    }

    public function getFailures(): Vector<Origin>
    {
        return $this->failures;
    }
}
