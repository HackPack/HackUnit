<?hh // strict
/*
 junit.rnc:
 #----------------------------------------------------------------------------------
 start = testsuite
 property = element property {
 attribute name {text},
 attribute value {text}
 }
 properties = element properties {
 property*
 }
 failure = element failure {
 attribute message {text},
 attribute type {text},
 text
 }
 testcase = element testcase {
 attribute classname {text},
 attribute name {text},
 attribute time {text},
 failure?
 }
 testsuite = element testsuite {
 attribute errors {xsd:integer},
 attribute failures {xsd:integer},
 attribute hostname {text},
 attribute name {text},
 attribute tests {xsd:integer},
 attribute time {xsd:double},
 attribute timestamp {xsd:dateTime},
 properties,
 testcase*,
 element system-out {text},
 element system-err {text}
 }
 #----------------------------------------------------------------------------------
 and junitreport.rnc
 #----------------------------------------------------------------------------------
 include "junit.rnc" {
 start = testsuites
 testsuite = element testsuite {
 attribute errors {xsd:integer},
 attribute failures {xsd:integer},
 attribute hostname {text},
 attribute name {text},
 attribute tests {xsd:integer},
 attribute time {xsd:double},
 attribute timestamp {xsd:dateTime},
 attribute id {text},
 attribute package {text},
 properties,
 testcase*,
 element system-out {text},
 element system-err {text}
 }
 }
 testsuites = element testsuites {
 testsuite*
 }
 */

namespace HackPack\HackUnit\Tests\Report;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Report\Format\JUnit;
use HackPack\HackUnit\Report\TestResult;
use HackPack\HackUnit\Report\Summary;
use HackPack\HackUnit\Report\SummaryBuilder;
use HackPack\HackUnit\Contract\Assert;
use SimpleXMLElement;

class JUnitTest {

  private resource $stream;
  private JUnit $formatter;
  private Summary $summary;

  public function __construct() {
    $this->stream = fopen('php://memory', 'r+');
    $this->formatter = new JUnit($this->stream);
    $this->summary = SummaryBuilder::emptySummary();
  }

  <<Test>>
  public function rootNodeIsCreated(Assert $assert): void {
    $this->formatter->writeReport($this->summary);
    $report = $this->getActualReport($assert);
    $assert->string($report->getName())->is('testsuites');
    $attributeCount = 0;
    foreach ($report->attributes() as $name => $value) {
      $attributeCount++;
    }
    $assert->int($attributeCount)->eq(0);
  }

  <<Test>>
  public function suiteReportsAreGenerated(Assert $assert): void {
    $this->summary['suite summaries'] = Map {
      'suite 1' => SummaryBuilder::emptySuiteSummary(),
      'suite 2' => SummaryBuilder::emptySuiteSummary(),
    };

    $this->formatter->writeReport($this->summary);
    $report = $this->getActualReport($assert);
    $assert->int(count($report->xpath('testsuite')))->eq(2);
  }

  <<Test>>
  public function executionTimeIsIncludedInSuiteReport(Assert $assert): void {
    $assert->skip('Need to time individual suites');
  }

  <<Test>>
  public function suiteNameIsIncluded(Assert $assert): void {
    $suiteSummary = SummaryBuilder::emptySuiteSummary();
    $this->summary['suite summaries'] = Map {'suite name' => $suiteSummary};
    $this->formatter->writeReport($this->summary);
    $report = $this->getActualReport($assert)->xpath('testsuite')[0];
    $assert->string((string) $report['name'])->is('suite name');
  }

  <<Test>>
  public function suiteCountsAreIncluded(Assert $assert): void {
    $suiteSummary = SummaryBuilder::emptySuiteSummary();
    $suiteSummary['fail count'] = 2;
    $suiteSummary['test count'] = 5;
    $this->summary['suite summaries'] = Map {'suite name' => $suiteSummary};

    $this->formatter->writeReport($this->summary);
    $report = $this->getActualReport($assert)->xpath('testsuite')[0];

    $assert->string((string) $report->offsetGet('failures'))->is('2');
    $assert->string((string) $report->offsetGet('tests'))->is('5');
  }

  <<Test>>
  public function testsAreIncluded(Assert $assert): void {
    $suiteSummary = SummaryBuilder::emptySuiteSummary();
    $suiteSummary['test summaries'] = Map {
      'test1' => SummaryBuilder::emptyTestSummary(),
      'test2' => SummaryBuilder::emptyTestSummary(),
    };
    $this->summary['suite summaries'] = Map {'suite name' => $suiteSummary};
    $this->formatter->writeReport($this->summary);
    $report = $this->getActualReport($assert)->xpath('testsuite')[0];

    $testCases = $report->xpath('testcase');
    $assert->int(count($testCases))->eq(2);
  }

  <<Test>>
  public function timingIsIncludedInTestReports(Assert $assert): void {
    $assert->skip('Need to time individual tests.');
  }

  <<Test>>
  public function testNameIsIncluded(Assert $assert): void {
    $suiteSummary = SummaryBuilder::emptySuiteSummary();
    $suiteSummary['test summaries'] = Map {
      'test name' => SummaryBuilder::emptyTestSummary(),
    };

    $summary = SummaryBuilder::emptySummary();
    $summary['suite summaries'] = Map {'suite name' => $suiteSummary};

    $this->formatter->writeReport($summary);
    $report = $this->getActualReport($assert);
    $testReport = $report->xpath('/testsuites/testsuite/testcase')[0];

    $assert->string((string) $testReport->offsetGet('name'))->is('test name');
  }

  <<Test>>
  public function testFailureIsIncluded(Assert $assert): void {

    $failEvent = Failure::fromCallStack('For jUnit report.');
    $testSummary = SummaryBuilder::emptyTestSummary();
    $testSummary['result'] = TestResult::Fail;
    $testSummary['fail event'] = $failEvent;

    $suiteSummary = SummaryBuilder::emptySuiteSummary();
    $suiteSummary['test summaries'] = Map {'test name' => $testSummary};

    $summary = SummaryBuilder::emptySummary();
    $summary['suite summaries'] = Map {'suite name' => $suiteSummary};

    $this->formatter->writeReport($summary);
    $report = $this->getActualReport($assert);
    $failElements = $report->xpath('/testsuites/testsuite/testcase/failure');
    $assert->int(count($failElements))->eq(1);

    $failure = $failElements[0];
    $assert->string((string) $failure->offsetGet('message'))
      ->is($failEvent->getMessage());
  }

  <<Test>>
  public function testSkipIsIncluded(Assert $assert): void {
    $testSummary = SummaryBuilder::emptyTestSummary();
    $testSummary['result'] = TestResult::Skip;

    $suiteSummary = SummaryBuilder::emptySuiteSummary();
    $suiteSummary['test summaries'] = Map {'test name' => $testSummary};

    $summary = SummaryBuilder::emptySummary();
    $summary['suite summaries'] = Map {'suite name' => $suiteSummary};

    $this->formatter->writeReport($summary);
    $report = $this->getActualReport($assert);
    $failElements = $report->xpath('/testsuites/testsuite/testcase/skip');
    $assert->int(count($failElements))->eq(1);
  }

  private function getActualReport(Assert $assert): SimpleXMLElement {
    rewind($this->stream);
    $rawXml = stream_get_contents($this->stream);
    try {
      $report = new SimpleXMLElement($rawXml);
    } catch (\Exception $e) {
      $assert->fail(
        sprintf(
          "XML error: %s\nOriginal XML:\n%s",
          $e->getMessage(),
          $rawXml,
        ),
      );
      throw new \RuntimeException('This should not be reached');
    }
    return $report;
  }
}
