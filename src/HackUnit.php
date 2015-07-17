<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit
{
    private static bool $failures = false;

    public static function fromCli(string $projectDir) : void
    {
        /*
         * Configuration
         */
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);

        /*
         * Test case setup
         */
        $testReporter = new Test\Reporter($clio);

        $suiteBuilder = (\ReflectionClass $c) ==> {
            return new Test\Suite($c, class_meth(Test\TestCase::class, 'build'));
        };

        $testLoader = new Test\Loader(
            $suiteBuilder,
            $options->includes,
            $options->excludes
        );
        $testLoader->onMalformedSuite(inst_meth($testReporter, 'reportMalformedSuite'));

        /*
         * Colors!
         */
        if($options->colors) {
            $testReporter->enableColors();
        }

        /*
         * Register events with the runner
         */
        $testRunner = new Test\Runner(class_meth(Assert::class, 'build'));

        // Allow us to set the exit code
        $testRunner->onFailure($event ==> {self::$failures = true;});

        // Allow the reporter to listen
        $testRunner->onFailure(inst_meth($testReporter, 'reportFailure'));
        $testRunner->onSkip(inst_meth($testReporter, 'reportSkip'));
        $testRunner->onSuccess(inst_meth($testReporter, 'reportSuccess'));
        $testRunner->onPass(inst_meth($testReporter, 'reportPass'));
        $testRunner->onUncaughtException(inst_meth($testReporter, 'reportUntestedException'));

        // Identify the package before running tests
        $testRunner->onRunStart(inst_meth($testReporter, 'identifyPackage'));

        // Start timing after identification
        $testRunner->onRunStart(inst_meth($testReporter, 'startTiming'));

        // Stop timing after tests
        $testRunner->onRunEnd(inst_meth($testReporter, 'displaySummary'));


        // LET'S DO THIS!
        $testRunner->run($testLoader->testSuites());

        // Exit codes FTW
        if(self::$failures) {
            exit(1);
        }
        exit(0);
    }
}
