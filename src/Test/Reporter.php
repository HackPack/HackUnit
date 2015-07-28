<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Util\Options;
use HackPack\HackUnit\Util\TraceItem;

use kilahm\Clio\BackgroundColor;
use kilahm\Clio\Format\Style;
use kilahm\Clio\Format\StyleGroup;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;

class Reporter
{
    private bool $colors = false;
    private ?float $starttime = null;

    private Vector<Failure> $failEvents = Vector{};
    private Vector<Skip> $skipEvents = Vector{};
    private Vector<MalformedSuite> $malformedEvents = Vector{};

    private int $assertCount = 0;
    private int $successCount = 0;
    private int $passCount = 0;
    private int $testCount = 0;

    public function __construct(private \kilahm\Clio\Clio $clio)
    {
    }

    public function identifyPackage() : void
    {
        $message = 'HackUnit by HackPack version ' . Options::VERSION;
        if($this->colors) {
            $message = $this->clio->style($message)->fg(TextColor::blue)->render();
        }
        $this->clio->line('');
        $this->clio->line($message);
        $this->clio->line('');
    }

    public function startTiming() : void
    {
        $this->starttime = microtime(true);
    }

    public function enableColors() : void
    {
        $this->colors = true;
    }

    public function reportFailure(Failure $event) : void
    {
        $this->failEvents->add($event);
        $this->testCount++;
        $this->assertCount++;
        $message = 'F';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::error());
        }
        $this->clio->show($message);
    }

    public function reportSkip(Skip $event) : void
    {
        $this->skipEvents->add($event);
        $this->testCount++;
        $message = 'S';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::warn());
        }
        $this->clio->show($message);
    }

    public function reportSuccess() : void
    {
        $this->successCount++;
        $this->assertCount++;
    }

    public function reportPass() : void
    {
        $message = '.';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::success());
        }
        $this->clio->show($message);
        $this->testCount++;
        $this->passCount++;
    }

    public function reportUntestedException(\Exception $e) : void
    {
        $message = 'Fatal exception thrown in ' . $e->getFile() . ' on line ' . $e->getLine() . '.';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::error());
        }

        $this->clio->line(PHP_EOL);
        $this->clio->line($message);
        $this->clio->line('Exception message:');
        $this->clio->line($e->getMessage());
        $this->clio->line('Trace:');
        $this->clio->line($e->getTraceAsString());
    }

    public function reportMalformedSuite(MalformedSuite $event) : void
    {
        $this->malformedEvents->add($event);
    }

    public function displaySummary() : void
    {
        // Blank line between the dots and the summary
        $this->clio->line(PHP_EOL);
        $this->clio->line($this->timeReport());
        $this->clio->line($this->testSummary());
        $this->clio->show($this->malformedReport());
        $this->clio->show($this->skipReport());
        $this->clio->show($this->errorReport());
    }

    public function testSummary() : string
    {
        $successCount = (string)$this->successCount;
        $passCount = (string)$this->passCount;
        $failedCount = (string)$this->failEvents->count();
        $skipCount = (string)$this->skipEvents->count();

        if($this->colors) {
            $successCount = $this->clio->style($successCount)->with(Style::success());
            $passCount = $this->clio->style($passCount)->with(Style::success());

            $failedStyle = $failedCount === '0' ? Style::success() : Style::error();
            $skipStyle = $skipCount === '0' ? Style::success() : Style::warn();

            $failedCount = $this->clio->style($failedCount)->with($failedStyle);
            $skipCount = $this->clio->style($skipCount)->with($skipStyle);
        }

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
        return PHP_EOL . $this->clio->style('Skipped tests:')->with(Style::warn()) . PHP_EOL .
            implode(PHP_EOL . PHP_EOL, $this->skipEvents->mapWithKey(($idx, $e) ==> {
                return implode(PHP_EOL, [
                    ($idx + 1) . ') ' . $this->buildMethodCall($e->callSite()),
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
                $this->clio
                    ->style(($idx + 1) . ') Test failure - ' . $testMethod)
                    ->with(Style::error())
                ,
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

        if($this->colors) {
            return $this->clio->style($message)->fg(TextColor::blue)->render();
        }
        return $message;
    }

    private function malformedReport() : string
    {
        if($this->malformedEvents->isEmpty()) {
            return '';
        }

        $report = 'Some test suites were malformed:';
        if($this->colors) {
            $report = $this->clio->style($report)->with(Style::warn());
        }

        foreach($this->malformedEvents as $idx => $event) {
            $report .= implode(PHP_EOL,[
                PHP_EOL,
                ($idx + 1) . ') ' . $this->buildMethodCall($event->traceItem()),
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

    private function buildMethodCall(TraceItem $item, ?StyleGroup $style = null) : string
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
        if($style !== null && $this->colors) {
            return $this->clio->style($out)->with($style);
        }
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
}
