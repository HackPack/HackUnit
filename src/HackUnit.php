<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit {
  private bool $failures = false;

  private Report\SummaryBuilder $summaryBuilder;

  public function __construct(
    private Traversable<Report\Format> $reportFormatters,
    private Report\Status $status,
    private Test\SuiteBuilder $suiteBuilder,
    private Test\Loader $loader,
    private Test\Runner $runner,
  ) {
    $this->summaryBuilder = new Report\SummaryBuilder();
  }

  public function run(): void {
    $this->loader->onBuildFailure(
      ($event) ==> {
        $this->failures = true;
        $this->status->handleBuildFailure($event);
      },
    );
    $this->suiteBuilder->onMalformedSuite(
      inst_meth($this->summaryBuilder, 'handleMalformedSuite'),
    );

    $this->runner->onRunStart(
      () ==> {
        $this->status->handleRunStart();
        $this->summaryBuilder->handleRunStart();
      },
    );

    $this->runner->onTestStart(
      ($e) ==> {
        $this->summaryBuilder->handleTestStart($e);
      },
    );

    $this->runner->onFailure(
      ($e) ==> {
        // Allow us to set the exit code
        $this->failures = true;
        $this->status->handleFailure($e);
        $this->summaryBuilder->handleFailure($e);

      },
    );
    $this->runner->onSkip(
      ($e) ==> {
        $this->status->handleSkip($e);
        $this->summaryBuilder->handleSkip($e);
      },
    );

    $this->runner->onSuccess(
      ($e) ==> {
        $this->summaryBuilder->handleSuccess($e);
      },
    );

    $this->runner->onPass(
      ($e) ==> {
        $this->status->handlePass($e);
        $this->summaryBuilder->handlePass($e);
      },
    );

    $this->runner->onUncaughtException(
      ($exception) ==> {
        $this->summaryBuilder->handleUntestedException($exception);
      },
    );
    $this->runner->onRunEnd(
      () ==> {
        $this->summaryBuilder->handleRunEnd();
        $summary = $this->summaryBuilder->getSummary();
        foreach ($this->reportFormatters as $formatter) {
          $formatter->writeReport($summary);
        }
      },
    );
    // LET'S DO THIS!
    $this->runner->run($this->loader->testSuites());

    // Exit codes FTW
    if ($this->failures) {
      exit(1);
    }
    exit(0);
  }
}
