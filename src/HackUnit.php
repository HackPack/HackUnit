<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit
{
    public static function fromCli(string $projectDir) : void
    {
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);


        $testReporter = new Test\Reporter($clio);
        if($options->colors) {
            $testReporter->withColor();
        }

        $suiteBuilder = (\ReflectionClass $c) ==> {
            return new Test\Suite($c, class_meth(Test\TestCase::class, 'build'));
        };

        $loader = new Util\Loader(
            $suiteBuilder,
            $options->includes,
            $options->excludes
        );
        $loader->onMalformedSuite(inst_meth($testReporter, 'reportMalformedSuite'));

        $runner = new Util\Runner(class_meth(Assert::class, 'build'));

        $runner->onFailure(inst_meth($testReporter, 'reportFailure'));
        $runner->onSkip(inst_meth($testReporter, 'reportSkip'));
        $runner->onSuccess(inst_meth($testReporter, 'reportSuccess'));
        $runner->onPass(inst_meth($testReporter, 'reportPass'));

        $runner->onRunStart(inst_meth($testReporter, 'identifyPackage'));
        $runner->onRunStart(inst_meth($testReporter, 'startTiming'));

        // This is done here to ensure the coverage report only includes the minimum support code as possible.
        if($options->coverage !== CoverageLevel::none) {
            $srcLoader = new Coverage\Loader(
                $options->sourceFolders,
            );
            $parser = new Coverage\Parser(
                class_meth(self::class, 'fileOutliner'),
                $srcLoader,
            );
            $driver = new Coverage\Driver\FbCoverageDriver();
            $coverage = new Coverage\Processor(
                $parser,
                $driver,
            );
            $runner->onRunStart(inst_meth($driver, 'start'));
            $runner->onRunEnd(inst_meth($driver, 'stop'));
        }

        $runner->onRunEnd(inst_meth($testReporter, 'displaySummary'));

        $runner->onUncaughtException(inst_meth($testReporter, 'reportUntestedException'));

        $runner->run($loader->testSuites());
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
