<?hh // strict

namespace HackPack\HackUnit\Report\Format;

use HackPack\HackUnit\Report\Format;
use HackPack\HackUnit\Report\Summary;
use HackPack\HackUnit\Report\TestResult;
use HackPack\HackUnit\Report\TestSummary;
use HackPack\HackUnit\Report\SuiteSummary;
use SimpleXMLElement;

final class JUnit implements Format {
  private SimpleXMLElement $report;

  public static function build(string $reportPath): this {
    return new self(fopen($reportPath, 'w'));
  }

  public function __construct(private resource $out) {
    $this->report = new SimpleXMLElement('<testsuites/>');
  }
  public function writeReport(Summary $summary): void {
    foreach ($summary['suite summaries'] as $name => $suiteSummary) {
      $this->report->addChild('testsuite')
        |> $this->populateSuiteReport($name, $suiteSummary, $$);
    }
    fwrite($this->out, $this->report->asXML());
  }

  private function populateSuiteReport(
    string $name,
    SuiteSummary $summary,
    SimpleXMLElement $report,
  ): void {
    $report->addAttribute('name', $name);
    $this->addSuiteCounts($summary, $report);
    foreach ($summary['test summaries'] as $testName => $testSummary) {
      $report->addChild('testcase')
        |> $this->populateTestReport($name, $testName, $testSummary, $$);
    }
  }

  private function addSuiteCounts(
    SuiteSummary $summary,
    SimpleXMLElement $report,
  ): void {
    $report->addAttribute('failures', $summary['fail count']);
    $report->addAttribute('tests', $summary['test count']);
  }

  private function populateTestReport(
    string $suiteName,
    string $name,
    TestSummary $summary,
    SimpleXMLElement $report,
  ): void {
    $report->addAttribute('name', $name);
    $report->addAttribute('classname', $suiteName);

    switch ($summary['result']) {
      case TestResult::Pass:
      case TestResult::Error:
        // Error is not used yet
        // Nothing more to add when test passes
        break;
      case TestResult::Fail:
        $event = $summary['fail event'];
        $message = $event === null ? 'Unknown failure' : $event->getMessage();
        $failElement = $report->addChild('failure');
        $failElement->addAttribute('message', $message);
        break;
      case TestResult::Skip:
        $report->addChild('skip');
        break;
    }
  }
}
