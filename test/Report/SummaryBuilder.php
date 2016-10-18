<?hh // strict

namespace HackPack\HackUnit\Tests\Report;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Report\TestResult;
use HackPack\HackUnit\Report\SuiteSummary;
use HackPack\HackUnit\Report\Summary;
use HackPack\HackUnit\Report\SummaryBuilder;
use HackPack\HackUnit\Util\TraceItem;

final class SummaryBuilderTest {

  private SummaryBuilder $builder;
  public function __construct() {
    $this->builder = new SummaryBuilder();

  }

  private function buildStackTraces(): (TraceItem, TraceItem) {
    return tuple(
      shape(
        'file' => 'test',
        'line' => 10,
        'function' => 'assertionFunction',
        'class' => 'TestClass',
      ),
      shape(
        'file' => 'test',
        'line' => 10,
        'function' => 'testFunction',
        'class' => 'TestClass',
      ),
    );
  }

  private function buildSuccessEvent(): Success {
    return new Success(...$this->buildStackTraces());
  }

  private function buildFailEvent(): Failure {
    return new Failure('failure message', ...$this->buildStackTraces());
  }

  private function buildSkipEvent(): Skip {
    return new Skip('skip message', ...$this->buildStackTraces());
  }

  <<Test>>
  public function successIncrementsAppropriateCounts(Assert $assert): void {
    $this->builder->handleSuccess($this->buildSuccessEvent());
    $actualSummary = $this->builder->getSummary();

    $expectedTestSummary = SummaryBuilder::emptyTestSummary();
    $expectedTestSummary['assert count'] = 1;
    $expectedTestSummary['success count'] = 1;

    $expectedSuiteSummary = SummaryBuilder::emptySuiteSummary();
    $expectedSuiteSummary['assert count'] = 1;
    $expectedSuiteSummary['success count'] = 1;
    $expectedSuiteSummary['test summaries'] = Map {
      'testFunction' => $expectedTestSummary,
    };

    $expectedSummary = SummaryBuilder::emptySummary();
    $expectedSummary['assert count'] = 1;
    $expectedSummary['success count'] = 1;
    $expectedSummary['suite summaries'] = Map {
      'TestClass' => $expectedSuiteSummary,
    };

    $this->compareSummaries($assert, $actualSummary, $expectedSummary);
  }

  <<Test>>
  public function failIncrementsAppropriateCounts(Assert $assert): void {
    $event = $this->buildFailEvent();
    $this->builder->handleFailure($event);
    $actualSummary = $this->builder->getSummary();

    $expectedTestSummary = SummaryBuilder::emptyTestSummary();
    $expectedTestSummary['assert count'] = 1;
    $expectedTestSummary['result'] = TestResult::Fail;
    $expectedTestSummary['fail event'] = $event;

    $expectedSuiteSummary = SummaryBuilder::emptySuiteSummary();
    $expectedSuiteSummary['assert count'] = 1;
    $expectedSuiteSummary['test count'] = 1;
    $expectedSuiteSummary['fail count'] = 1;
    $expectedSuiteSummary['test summaries'] = Map {
      'testFunction' => $expectedTestSummary,
    };

    $expectedSummary = SummaryBuilder::emptySummary();
    $expectedSummary['assert count'] = 1;
    $expectedSummary['test count'] = 1;
    $expectedSummary['fail count'] = 1;
    $expectedSummary['fail events'] = Vector {$event};
    $expectedSummary['suite summaries'] = Map {
      'TestClass' => $expectedSuiteSummary,
    };

    $this->compareSummaries($assert, $actualSummary, $expectedSummary);
  }

  <<Test>>
  public function skipIncrementsAppropriateCounts(Assert $assert): void {
    $event = $this->buildSkipEvent();
    $this->builder->handleSkip($event);
    $actualSummary = $this->builder->getSummary();

    $expectedTestSummary = SummaryBuilder::emptyTestSummary();
    $expectedTestSummary['test count'] = 1;
    $expectedTestSummary['result'] = TestResult::Skip;
    $expectedTestSummary['skip event'] = $event;

    $expectedSuiteSummary = SummaryBuilder::emptySuiteSummary();
    $expectedSuiteSummary['skip count'] = 1;
    $expectedSuiteSummary['test count'] = 1;
    $expectedSuiteSummary['test summaries'] = Map {
      'testFunction' => $expectedTestSummary,
    };

    $expectedSummary = SummaryBuilder::emptySummary();
    $expectedSummary['test count'] = 1;
    $expectedSummary['skip count'] = 1;
    $expectedSummary['skip events'] = Vector {$event};
    $expectedSummary['suite summaries'] = Map {
      'TestClass' => $expectedSuiteSummary,
    };

    $this->compareSummaries($assert, $actualSummary, $expectedSummary);
  }

  private function compareSummaries(
    Assert $assert,
    Summary $actualSummary,
    Summary $expectedSummary,
  ): void {
    $assert->float($actualSummary['start time'])
      ->eq($expectedSummary['start time']);
    $assert->float($actualSummary['end time'])
      ->eq($expectedSummary['end time']);
    $assert->int($actualSummary['assert count'])
      ->eq($expectedSummary['assert count']);
    $assert->int($actualSummary['success count'])
      ->eq($expectedSummary['success count']);
    $assert->int($actualSummary['pass count'])
      ->eq($expectedSummary['pass count']);
    $assert->int($actualSummary['fail count'])
      ->eq($expectedSummary['fail count']);
    $assert->int($actualSummary['skip count'])
      ->eq($expectedSummary['skip count']);
    $assert->int($actualSummary['test count'])
      ->eq($expectedSummary['test count']);
    $assert->container($actualSummary['fail events'])
      ->containsOnly($expectedSummary['fail events']);
    $assert->container($actualSummary['skip events'])
      ->containsOnly($expectedSummary['skip events']);
    $assert->container($actualSummary['malformed events'])
      ->containsOnly($expectedSummary['malformed events']);
    $assert->container($actualSummary['untested exceptions'])
      ->containsOnly($expectedSummary['untested exceptions']);

    $assert->container($actualSummary['suite summaries']->keys())
      ->containsOnly($expectedSummary['suite summaries']->keys());
    foreach ($expectedSummary['suite summaries'] as
             $suiteName => $expectedSuiteSummary) {
      $this->compareSuiteSummaries(
        $assert,
        $actualSummary['suite summaries']->at($suiteName),
        $expectedSuiteSummary,
      );
    }
  }

  private function compareSuiteSummaries(
    Assert $assert,
    SuiteSummary $actualSummary,
    SuiteSummary $expectedSummary,
  ): void {
    $assert->int($actualSummary['assert count'])
      ->eq($expectedSummary['assert count']);
    $assert->int($actualSummary['success count'])
      ->eq($expectedSummary['success count']);
    $assert->int($actualSummary['pass count'])
      ->eq($expectedSummary['pass count']);
    $assert->int($actualSummary['fail count'])
      ->eq($expectedSummary['fail count']);
    $assert->int($actualSummary['skip count'])
      ->eq($expectedSummary['skip count']);
    $assert->int($actualSummary['test count'])
      ->eq($expectedSummary['test count']);

    $assert->container($actualSummary['test summaries']->keys())
      ->containsOnly($expectedSummary['test summaries']->keys());
  }
}
