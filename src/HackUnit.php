<?hh // strict

namespace HackPack\HackUnit;

require_once dirname(__DIR__) . '/vendor/autoload.php';

final class HackUnit {
  private static bool $failures = false;

  public static function run(Util\Options $options): void {

    /*
     * Test case setup
     */
    $testReporter = new Test\Reporter();

    $suiteBuilder = new Test\SuiteBuilder(
      ($className, $fileName) ==> new Test\Parser($className, $fileName),
    );
    $suiteBuilder->onMalformedSuite(
      inst_meth($testReporter, 'reportMalformedSuite'),
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
    $testRunner->onRunStart(inst_meth($testReporter, 'identifyPackage'));

    // Start timing after identification
    $testRunner->onRunStart(inst_meth($testReporter, 'startTiming'));

    // Allow us to set the exit code
    $testRunner->onFailure(
      $event ==> {
        self::$failures = true;
      },
    );

    // Allow the reporter to listen
    $testRunner->onFailure(inst_meth($testReporter, 'reportFailure'));
    $testRunner->onSkip(inst_meth($testReporter, 'reportSkip'));
    $testRunner->onSuccess(inst_meth($testReporter, 'reportSuccess'));
    $testRunner->onPass(inst_meth($testReporter, 'reportPass'));
    $testRunner->onUncaughtException(
      inst_meth($testReporter, 'reportUntestedException'),
    );

    // Stop timing after tests
    $testRunner->onRunEnd(inst_meth($testReporter, 'displaySummary'));

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
    $includes = Set{
      $root . '/test',
    };
    $excludes = Set{
      $root . '/test/Fixtures',
      $root . '/test/Doubles',
    };
    $options = new Util\Options($includes, $excludes);
    self::run($options);
  }
}
