<?hh // strict

namespace HackPack\HackUnit\Report\Format;

use HackPack\HackUnit\Report\Format;
use HackPack\HackUnit\Report\Summary;
use HackPack\HackUnit\Util\TraceItem;

class Cli implements Format {
  public function __construct(private resource $out) {}

  public function writeReport(Summary $status): void {

    $this->line(PHP_EOL);
    $this->line($this->timeReport($status));
    $this->line($this->testSummary($status));
    $this->show($this->malformedReport($status));
    $this->show($this->skipReport($status));
    $this->show($this->errorReport($status));
    $this->show($this->untestedExceptionReport($status));
  }
  public function untestedExceptionReport(Summary $summary): string {
    $entries = Vector {};
    foreach ($summary['untested exceptions'] as $e) {

      $message =
        'Fatal exception thrown in '.
        $e->getFile().
        ' on line '.
        $e->getLine().
        '.';

      $entries->add(
        implode(
          PHP_EOL,
          [
            $message,
            'Exception message:',
            $e->getMessage(),
            'Trace:',
            $e->getTraceAsString(),
          ],
        ),
      );
    }

    return PHP_EOL.implode(PHP_EOL, $entries).PHP_EOL;
  }

  public function testSummary(Summary $summary): string {
    return sprintf(
      'Assertions: %s/%d Tests: %s/%d Failed: %s Skipped %s',
      $summary['success count'],
      $summary['assert count'],
      $summary['pass count'],
      $summary['test count'],
      $summary['fail count'],
      $summary['skip count'],
    );
  }

  public function skipReport(Summary $summary): string {
    if ($summary['skip events']->isEmpty()) {
      return '';
    }
    return
      PHP_EOL.
      'Skipped tests:'.
      PHP_EOL.
      implode(
        PHP_EOL.PHP_EOL,
        $summary['skip events']->mapWithKey(
          ($idx, $e) ==> {
            return implode(
              PHP_EOL,
              [
                '-*-*-*- Test Skip '.($idx + 1).' -*-*-*-',
                $this->buildMethodCall($e->testMethodTraceItem()),
                '  In file '.$e->testMethodTraceItem()['file'],
                $e->message(),
              ],
            );
          },
        ),
      ).
      PHP_EOL;

  }

  public function errorReport(Summary $summary): string {
    if ($summary['fail events']->isEmpty()) {
      return '';
    }
    $report = '';
    foreach ($summary['fail events'] as $idx => $e) {
      $assertionTraceItem = $e->assertionTraceItem();
      $testTraceItem = $e->testMethodTraceItem();
      $assertionCall = $this->buildMethodCall($assertionTraceItem);
      $testMethod = $this->buildMethodCall($testTraceItem);
      $report .=
        implode(
          PHP_EOL,
          [
            '',
            '-*-*-*- Test Failure '.($idx + 1).' -*-*-*-',
            'Test failure - '.$testMethod,
            'Assertion failed in '.$assertionCall,
            'On line '.
            $assertionTraceItem['line'].
            ' of '.
            $assertionTraceItem['file'],
            $e->getMessage(),
          ],
        ).
        PHP_EOL;
    }
    return $report.PHP_EOL;
  }

  private function timeReport(Summary $summary): string {
    $elapsedTime = $summary['end time'] - $summary['start time'];
    if ($elapsedTime < 0.000000001) {
      return 'Finished testing.';
    }
    return sprintf('Finished testing in %.2f seconds.', $elapsedTime);
  }

  private function malformedReport(Summary $summary): string {
    if ($summary['malformed events']->isEmpty()) {
      return '';
    }

    $report = 'Some test suites were malformed:';

    foreach ($summary['malformed events'] as $idx => $event) {
      $report .= implode(
        PHP_EOL,
        [
          PHP_EOL,
          '-*-*-*- Malformed Error '.($idx + 1).' -*-*-*-',
          $this->buildMethodCall($event->traceItem()),
          $this->buildLineReference($event->traceItem()),
          $event->message(),
        ],
      );
    }

    return $report.PHP_EOL;
  }

  private function buildLineReference(TraceItem $item): string {
    $lineNumber = $item['line'];
    $fileName = $item['file'];
    $lineNumber = $lineNumber === null ? '??' : (string) $lineNumber;
    $fileName = $fileName === null ? 'Unknown file' : (string) $fileName;
    return 'On line '.$lineNumber.' in file '.$fileName;
  }

  private function buildMethodCall(TraceItem $item): string {
    $className = $item['class'];
    $methodName = $item['function'];
    if ($className === null) {
      $className = 'Unknown class';
    }
    if ($methodName === null) {
      $methodName = 'Unknown method';
    }
    $out = $className.'->'.$methodName.'()';
    return $out;
  }

  private function line(string $message): void {
    fwrite($this->out, $message.PHP_EOL);
  }

  private function show(string $message): void {
    fwrite($this->out, $message);
  }
}
