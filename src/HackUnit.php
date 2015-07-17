<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit
{
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

        $testRunner->onFailure(inst_meth($testReporter, 'reportFailure'));
        $testRunner->onSkip(inst_meth($testReporter, 'reportSkip'));
        $testRunner->onSuccess(inst_meth($testReporter, 'reportSuccess'));
        $testRunner->onPass(inst_meth($testReporter, 'reportPass'));

        $testRunner->onRunStart(inst_meth($testReporter, 'identifyPackage'));
        $testRunner->onRunStart(inst_meth($testReporter, 'startTiming'));

        $testRunner->onRunEnd(inst_meth($testReporter, 'displaySummary'));

        $testRunner->onUncaughtException(inst_meth($testReporter, 'reportUntestedException'));

        $testRunner->run($testLoader->testSuites());
    }
}
