<?hh // strict

namespace HackPack\HackUnit\Report;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Event\Pass;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Util\Options;
use HackPack\HackUnit\Util\TraceItem;

type MutableSummary = shape(
  'start time' => float,
  'end time' => float,
  'assert count' => int,
  'success count' => int,
  'pass count' => int,
  'fail count' => int,
  'fail events' => Vector<Failure>,
  'skip count' => int,
  'skip events' => Vector<Skip>,
  'test count' => int,
  'malformed events' => Vector<MalformedSuite>,
  'untested exceptions' => Vector<\Exception>,
  'suite summaries' => Map<string, MutableSuiteSummary>,
);
type Summary = shape(
  'start time' => float,
  'end time' => float,
  'assert count' => int,
  'success count' => int,
  'pass count' => int,
  'fail count' => int,
  'fail events' => \ConstVector<Failure>,
  'skip count' => int,
  'skip events' => \ConstVector<Skip>,
  'test count' => int,
  'malformed events' => \ConstVector<MalformedSuite>,
  'untested exceptions' => \ConstVector<\Exception>,
  'suite summaries' => \ConstMap<string, SuiteSummary>,
);

type MutableSuiteSummary = shape(
  'assert count' => int,
  'success count' => int,
  'pass count' => int,
  'fail count' => int,
  'skip count' => int,
  'test count' => int,
  'test summaries' => Map<string, TestSummary>,
);

type SuiteSummary = shape(
  'assert count' => int,
  'success count' => int,
  'pass count' => int,
  'fail count' => int,
  'skip count' => int,
  'test count' => int,
  'test summaries' => \ConstMap<string, TestSummary>,
);

type TestSummary = shape(
  'assert count' => int,
  'success count' => int,
  'result' => TestResult,
  'skip event' => ?Skip,
  'fail event' => ?Failure,
);

type TestInfo = shape(
  'test name' => string,
  'suite name' => string,
);

enum TestResult : string {
  Pass = 'pass';
  Fail = 'fail';
  Skip = 'skip';
  Error = 'error';
}

class SummaryBuilder {

  // Mirror Summary shape, but with mutable collections
  private MutableSummary $summary;
  public function __construct() {
    $this->summary = self::emptyMutableSummary();
  }

  public function startTiming(): void {
    $this->summary['start time'] = microtime(true);
  }

  public function stopTiming(): void {
    $this->summary['end time'] = microtime(true);
  }

  public function handleFailure(Failure $event): void {
    $testInfo = $this->determineTestInfo($event->testMethodTraceItem());
    $this->ensureTestExists($testInfo);

    $this->summary['test count']++;
    $this->summary['fail count']++;
    $this->summary['assert count']++;
    $this->summary['fail events']->add($event);

    $suiteSummary =
      $this->summary['suite summaries']->at($testInfo['suite name']);
    $suiteSummary['test count']++;
    $suiteSummary['fail count']++;
    $suiteSummary['assert count']++;

    $testSummary =
      $suiteSummary['test summaries']->at($testInfo['test name']);
    $testSummary['fail event'] = $event;
    $testSummary['assert count']++;
    $testSummary['result'] = TestResult::Skip;

    $suiteSummary['test summaries']
      ->set($testInfo['test name'], $testSummary);
    $this->summary['suite summaries']
      ->set($testInfo['suite name'], $suiteSummary);

  }

  public function handleSkip(Skip $event): void {
    $testInfo = $this->determineTestInfo($event->testMethodTraceItem());
    $this->ensureTestExists($testInfo);

    $this->summary['test count']++;
    $this->summary['skip count']++;
    $this->summary['skip events']->add($event);

    $suiteSummary =
      $this->summary['suite summaries']->at($testInfo['suite name']);
    $suiteSummary['test count']++;
    $suiteSummary['skip count']++;

    $testSummary =
      $suiteSummary['test summaries']->at($testInfo['test name']);
    $testSummary['skip event'] = $event;
    $testSummary['result'] = TestResult::Skip;

    $suiteSummary['test summaries']
      ->set($testInfo['test name'], $testSummary);
    $this->summary['suite summaries']
      ->set($testInfo['suite name'], $suiteSummary);
  }

  public function handleSuccess(Success $event): void {
    $testInfo = $this->determineTestInfo($event->testMethodTraceItem());
    $this->ensureTestExists($testInfo);

    $this->summary['assert count']++;
    $this->summary['success count']++;

    $suiteSummary =
      $this->summary['suite summaries']->at($testInfo['suite name']);
    $suiteSummary['assert count']++;
    $suiteSummary['success count']++;

    $testSummary =
      $suiteSummary['test summaries']->at($testInfo['test name']);
    $testSummary['assert count']++;
    $testSummary['success count']++;

    $suiteSummary['test summaries']
      ->set($testInfo['test name'], $testSummary);
    $this->summary['suite summaries']
      ->set($testInfo['suite name'], $suiteSummary);
  }

  public function handlePass(Pass $event): void {
    $testInfo = $this->determineTestInfo($event->testMethodTraceItem());
    $this->ensureTestExists($testInfo);

    $this->summary['test count']++;
    $this->summary['pass count']++;

    $this->summary['suite summaries']
      ->at($testInfo['suite name'])['test count']++;
    $this->summary['suite summaries']
      ->at($testInfo['suite name'])['pass count']++;
  }

  public function handleUntestedException(\Exception $e): void {
    $this->summary['untested exceptions']->add($e);
  }

  public function handleMalformedSuite(MalformedSuite $event): void {
    $this->summary['malformed events']->add($event);
  }

  public function getSummary(): Summary {
    return $this->summary;
  }

  public static function emptySummary(): Summary {
    return self::emptyMutableSummary();
  }

  private static function emptyMutableSummary(): MutableSummary {
    return shape(
      'start time' => 0.0,
      'end time' => 0.0,
      'assert count' => 0,
      'success count' => 0,
      'pass count' => 0,
      'fail count' => 0,
      'fail events' => Vector {},
      'skip count' => 0,
      'skip events' => Vector {},
      'test count' => 0,
      'malformed events' => Vector {},
      'untested exceptions' => Vector {},
      'suite summaries' => Map {},
    );
  }

  public static function emptySuiteSummary(): SuiteSummary {
    return self::emptyMutableSuiteSummary();
  }

  private static function emptyMutableSuiteSummary(): MutableSuiteSummary {

    return shape(
      'assert count' => 0,
      'success count' => 0,
      'pass count' => 0,
      'fail count' => 0,
      'skip count' => 0,
      'test count' => 0,
      'test summaries' => Map {},
    );
  }

  public static function emptyTestSummary(): TestSummary {
    return shape(
      'assert count' => 0,
      'success count' => 0,
      'message' => '',
      'result' => TestResult::Pass,
    );
  }

  private function determineTestInfo(TraceItem $trace): TestInfo {
    $testName = Shapes::idx($trace, 'function', '??');
    if ($testName === null) {
      $testName = '??';
    }
    $suiteName =
      Shapes::idx($trace, 'class', Shapes::idx($trace, 'file', '??'));
    if ($suiteName === null) {
      $suiteName = '??';
    }

    return shape('test name' => $testName, 'suite name' => $suiteName);
  }

  private function ensureTestExists(TestInfo $testInfo): void {
    if (!$this->summary['suite summaries']
          ->containsKey($testInfo['suite name'])) {
      $this->summary['suite summaries']
        ->set($testInfo['suite name'], self::emptyMutableSuiteSummary());
    }
    if (!$this->summary['suite summaries']
          ->at($testInfo['suite name'])['test summaries']
          ->containsKey($testInfo['test name'])) {
      $this->summary['suite summaries']
        ->at($testInfo['suite name'])['test summaries']
        ->set($testInfo['test name'], self::emptyTestSummary());
    }
  }

}
