<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Util\Options;
use HackPack\HackUnit\Util\TraceItem;

class Reporter
{
    private ?float $starttime = null;

    private Vector<Failure> $failEvents = Vector{};
    private Vector<Skip> $skipEvents = Vector{};
    private Vector<MalformedSuite> $malformedEvents = Vector{};
    private Vector<\Exception> $untestedExceptions = Vector{};

    private int $assertCount = 0;
    private int $successCount = 0;
    private int $passCount = 0;
    private int $testCount = 0;

    public function __construct()
    {
    }

    public function identifyPackage() : void
    {
        $this->line('');
        $this->line('HackUnit by HackPack version ' . Options::VERSION);
        $this->line('');
    }

    public function startTiming() : void
    {
        $this->starttime = microtime(true);
    }

    public function reportFailure(Failure $event) : void
    {
        $this->failEvents->add($event);
        $this->testCount++;
        $this->assertCount++;
        $message = 'F';
        $this->show($message);
    }

    public function reportSkip(Skip $event) : void
    {
        $this->skipEvents->add($event);
        $this->testCount++;
        $message = 'S';
        $this->show($message);
    }

    public function reportSuccess() : void
    {
        $this->successCount++;
        $this->assertCount++;
    }

    public function reportPass() : void
    {
        $message = '.';
        $this->show($message);
        $this->testCount++;
        $this->passCount++;
    }

    public function reportUntestedException(\Exception $e) : void
    {
        $this->untestedExceptions->add($e);
    }

    public function untestedExceptionReport() : string
    {
        $entries = Vector{};
        foreach($this->untestedExceptions as $e) {

            $message = 'Fatal exception thrown in ' . $e->getFile() . ' on line ' . $e->getLine() . '.';

            $entries->add(implode(PHP_EOL, [
                $message,
                'Exception message:',
                $e->getMessage(),
                'Trace:',
                $e->getTraceAsString(),
            ]));
        }

        return PHP_EOL . implode(PHP_EOL, $entries) . PHP_EOL;
    }

    public function reportMalformedSuite(MalformedSuite $event) : void
    {
        $this->malformedEvents->add($event);
    }

    public function displaySummary() : void
    {
        // Blank line between the dots and the summary
        $this->line(PHP_EOL);
        $this->line($this->timeReport());
        $this->line($this->testSummary());
        $this->show($this->malformedReport());
        $this->show($this->skipReport());
        $this->show($this->errorReport());
        $this->show($this->untestedExceptionReport());
    }

    public function testSummary() : string
    {
        $successCount = (string)$this->successCount;
        $passCount = (string)$this->passCount;
        $failedCount = (string)$this->failEvents->count();
        $skipCount = (string)$this->skipEvents->count();

        return sprintf(
            'Assertions: %s/%d Tests: %s/%d Failed: %s Skipped %s',
            $successCount,
            $this->assertCount,
            $passCount,
            $this->testCount,
            $failedCount,
            $skipCount,
        );
    }

    public function skipReport() : string
    {
        if($this->skipEvents->isEmpty()) {
            return '';
        }
        return PHP_EOL . 'Skipped tests:' . PHP_EOL .
            implode(PHP_EOL . PHP_EOL, $this->skipEvents->mapWithKey(($idx, $e) ==> {
                return implode(PHP_EOL, [
                    '-*-*-*- Test Skip ' . ($idx + 1) . ' -*-*-*-',
                    $this->buildMethodCall($e->callSite()),
                    '  In file ' . $e->callSite()['file'],
                    $e->message(),
                ]);
            })) .
            PHP_EOL;

    }

    public function errorReport() : string
    {
        if($this->failEvents->isEmpty()) {
            return '';
        }
        $report = '';
        foreach($this->failEvents as $idx => $e) {
            $assertionTraceItem = $e->assertionTraceItem();
            $testTraceItem = $e->testMethodTraceItem();
            $assertionCall= $this->buildMethodCall($assertionTraceItem);
            $testMethod = $this->buildMethodCall($testTraceItem);
            $report .= implode(PHP_EOL,[
                '',
                '-*-*-*- Test Failure ' . ($idx + 1) . ' -*-*-*-',
                'Test failure - ' . $testMethod,
                'Assertion failed in ' . $assertionCall,
                'On line ' . $assertionTraceItem['line'] . ' of ' . $assertionTraceItem['file'],
                $e->getMessage(),
            ]) . PHP_EOL;
        }
        return $report . PHP_EOL;
    }

    private function timeReport() : string
    {
        if($this->starttime !== null) {
            $start = $this->starttime;
            $message = sprintf('Finished testing in %.2f seconds.', (float)(microtime(true) - $start));
        } else {
            $message = 'Finished testing.';
        }

        return $message;
    }

    private function malformedReport() : string
    {
        if($this->malformedEvents->isEmpty()) {
            return '';
        }

        $report = 'Some test suites were malformed:';

        foreach($this->malformedEvents as $idx => $event) {
            $report .= implode(PHP_EOL,[
                PHP_EOL,
                '-*-*-*- Malformed Error ' . ($idx + 1) . ' -*-*-*-',
                $this->buildMethodCall($event->traceItem()),
                $this->buildLineReference($event->traceItem()),
                $event->message(),
            ]);
        }

        return $report . PHP_EOL;
    }

    private function buildLineReference(TraceItem $item) : string
    {
        $lineNumber = $item['line'];
        $fileName = $item['file'];
        $lineNumber = $lineNumber === null ? '??' : (string)$lineNumber;
        $fileName = $fileName === null ? 'Unknown file' : (string)$fileName;
        return 'On line ' . $lineNumber . ' in file ' . $fileName;
    }

    private function buildMethodCall(TraceItem $item) : string
    {
        $className = $item['class'];
        $methodName = $item['function'];
        if($className === null) {
            $className = 'Unknown class';
        }
        if($methodName === null) {
            $methodName = 'Unknown method';
        }
        $out = $className . '->' . $methodName . '()';
        return $out;
    }

    private function captureVariable(mixed $var) : string
    {
        if($var === null) {
            return 'null';
        }

        ob_start();
        var_dump($var);
        $report = ob_get_contents();
        ob_end_clean();
        $trimmed = rtrim($report, PHP_EOL);
        if(strpos($trimmed, PHP_EOL)) {
            return PHP_EOL . $trimmed;
        }
        return $trimmed;
    }

    private function line(string $message) : void
    {
         echo $message . PHP_EOL;
    }

    private function show(string $message) : void
    {
         echo $message;
    }
}
