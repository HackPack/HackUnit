<?hh //strict
namespace HackUnit\Core;

type Failure = shape('method' => string, 'message' => string, 'location' => string);

class TestResult
{
    protected Vector<Failure> $failures;

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
        $trace = $exception->getTrace();
        $fileInfo = $this->getFileAndLine($trace);
        $test = $trace[1];
        $this->failures->add(shape(
            'method' => sprintf('%s::%s', $test['class'], $test['function']),
            'message' => $exception->getMessage(),
            'location' => sprintf('%s:%d', $fileInfo[0], $fileInfo[1])
        ));
        $this->errorCount++;
    }

    protected function getFileAndLine(array<array<string, string>> $trace): Pair<string, string>
    {
        foreach ($trace as $item) {
            if (array_key_exists('line', $item)) {
                return Pair {$item['file'], $item['line']};
            }
        }
        return Pair {'',''};
    }

    public function getSummary(): string
    {
        return sprintf("%d run, %d failed", $this->runCount, $this->errorCount);
    }

    public function getFailures(): Vector<Failure>
    {
        return $this->failures;
    }
}
