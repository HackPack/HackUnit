<?hh //strict
namespace HackPack\HackUnit\Core;

use HackPack\HackUnit\Error\TraceParser;
use HackPack\HackUnit\Error\Origin;
use HackPack\Hacktions\EventEmitter;

class TestResult
{
    use EventEmitter;

    protected Vector<Origin> $failures = Vector{};
    protected ?float $startTime;
    protected ?float $endTime;
    protected int $failCount = 0;
    protected int $skipCount = 0;
    protected int $runCount = 0;
    protected int $groupCount = 0;

    public function __construct()
    {
    }

    public function groupStarted() : void
    {
        $this->groupCount++;
    }

    public function testStarted(): void
    {
        $this->runCount++;
    }

    public function startTimer(): void
    {
        $this->startTime = microtime(true);
        $this->endTime = null;
    }

    public function stopTimer(): void
    {
        if($this->endTime === null) {
            $this->endTime = microtime(true);
        }
    }

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    public function getTime(): float
    {
        $end = $this->endTime === null ? microtime(true) : $this->endTime;
        $start = $this->startTime;
        if($start === null) {
            throw new \LogicException('Timer must be started before the test run time is computed.');
        }

        return (float)($end - $start);
    }

    public function testPassed(): void
    {
        $this->trigger('testPassed');
    }

    public function testError(\Exception $exception): void
    {
        //TODO: track this
        echo $exception->getMessage();
    }

    public function groupError(\Exception $exception): void
    {
        //TODO: track this
        echo $exception->getMessage();
    }

    public function testFailed(\Exception $exception): void
    {
        $parser = new TraceParser($exception);
        $this->failures->add($parser->getOrigin());
        $this->failCount++;
        $this->trigger('testFailed');
    }

    public function testSkipped(\Exception $exception): void
    {
        $this->skipCount++;
        $this->trigger('testSkipped');
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

    public function failCount(): int
    {
        return $this->failCount;
    }

    public function skipCount(): int
    {
        return $this->skipCount;
    }

    public function groupCount(): int
    {
        return $this->groupCount;
    }

    public function testCount(): int
    {
        return $this->runCount;
    }

}
