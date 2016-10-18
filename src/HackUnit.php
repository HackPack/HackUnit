<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit {
  private static bool $failures = false;

  public static function run(Util\Options $options): void {

    $reportFormatters = Vector {new Report\Format\Cli(STDOUT)};
    $summaryBuilder = new Report\SummaryBuilder();
    $status = new Report\Status(STDOUT);
    /*
     * Test case setup
     */

    $suiteBuilder = new Test\SuiteBuilder(
      ($className, $fileName) ==> new Test\Parser($className, $fileName),
    );
    $suiteBuilder->onMalformedSuite(
      inst_meth($summaryBuilder, 'handleMalformedSuite'),
    );

    $testLoader = new Test\Loader(
      $class ==> $suiteBuilder->buildSuites($class),
      $options->includes->toSet(),
      $options->excludes->toSet(),
    );

    /*
     * Register events with the runner
     */
    $testRunner = new Test\Runner(class_meth(Assert::class, 'build'));

    // Identify the package before running tests
    $testRunner->onRunStart(inst_meth($status, 'handleRunStart'))
      ->onRunStart(inst_meth($summaryBuilder, 'startTiming')) // Start timing after identification
      ->onFailure( // Allow us to set the exit code
        $event ==> {
          self::$failures = true;
        },
      )
      ->onFailure(inst_meth($status, 'handleFailure'))
      ->onFailure(inst_meth($summaryBuilder, 'handleFailure'))
      ->onSkip(inst_meth($status, 'handleSkip'))
      ->onSkip(inst_meth($summaryBuilder, 'handleSkip'))
      ->onSuccess(inst_meth($summaryBuilder, 'handleSuccess'))
      ->onPass(inst_meth($status, 'handlePass'))
      ->onPass(inst_meth($summaryBuilder, 'handlePass'))
      ->onUncaughtException(
        inst_meth($summaryBuilder, 'handleUntestedException'),
      )
      ->onRunEnd(inst_meth($summaryBuilder, 'stopTiming'))
      ->onRunEnd(
        () ==> {
          $summary = $summaryBuilder->getSummary();
          foreach ($reportFormatters as $formatter) {
            $formatter->writeReport($summary);
          }
        },
      );

    // LET'S DO THIS!
    $testRunner->run($testLoader->testSuites());

    // Exit codes FTW
    if (self::$failures) {
      exit(1);
    }
    exit(0);
  }

  public static function selfTest(): void {
    $root = dirname(__DIR__);
    /* HH_FIXME[1002] */
    require_once $root.'/vendor/autoload.php';

    $includes = Set {$root.'/test'};
    $excludes = Set {$root.'/test/Fixtures', $root.'/test/Doubles'};
    $options = new Util\Options($includes, $excludes);

    self::run($options);
  }
}
