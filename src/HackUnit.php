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
         * Coverage setup
         */
        $srcLoader = new Coverage\Loader(
            $options->sourceFolders,
        );
        $parser = new Coverage\Parser(
            class_meth(self::class, 'fileOutliner'),
            $srcLoader,
        );
        $driver = new Coverage\Driver\FbCoverageDriver();
        $processor = new Coverage\Processor(
            $parser,
            $driver,
        );
        $coverageReporter = new Coverage\Reporter(
            $options->coverage,
            $processor,
            $clio,
        );

        /*
         * Test case setup
         */
        $testReporter = new Test\Reporter($clio);
        if($options->colors) {
            $testReporter->withColor();
        }

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
         * Register events with the runner
         */
        $testRunner = new Test\Runner(class_meth(Assert::class, 'build'));

        $testRunner->onFailure(inst_meth($testReporter, 'reportFailure'));
        $testRunner->onSkip(inst_meth($testReporter, 'reportSkip'));
        $testRunner->onSuccess(inst_meth($testReporter, 'reportSuccess'));
        $testRunner->onPass(inst_meth($testReporter, 'reportPass'));

        $testRunner->onRunStart(inst_meth($testReporter, 'identifyPackage'));
        $testRunner->onRunStart(inst_meth($testReporter, 'startTiming'));

        // This is done here to ensure the coverage report only includes the minimum support code as possible.
        if($options->coverage !== CoverageLevel::none) {
            $testRunner->onRunStart(inst_meth($driver, 'start'));
            $testRunner->onRunEnd(inst_meth($driver, 'stop'));
            $testRunner->onRunEnd(inst_meth($coverageReporter, 'showReport'));
        }

        $testRunner->onRunEnd(inst_meth($testReporter, 'displaySummary'));

        $testRunner->onUncaughtException(inst_meth($testReporter, 'reportUntestedException'));

        $testRunner->run($testLoader->testSuites());
    }

    public static function fileOutliner(string $path) : string
    {
        $outline = shell_exec('hh_client --json --from HackUnit --outline < ' . escapeshellarg($path));
        if(is_string($outline)) {
            return $outline;
        }
        return '';
    }
}
