<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit
{
    public static function fromCli(string $projectDir) : void
    {
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);


        $reporter = new Util\Reporter($clio);
        if($options->colors) {
            $reporter->withColor();
        }

        $suiteBuilder = (\ReflectionClass $c) ==> {
            return new Test\Suite($c, class_meth(Test\TestCase::class, 'build'));
        };

        $loader = new Util\Loader(
            $suiteBuilder,
            $options->includes,
            $options->excludes
        );
        $loader->onMalformedSuite(inst_meth($reporter, 'reportMalformedSuite'));

        $runner = new Util\Runner(class_meth(Assert::class, 'build'));

        $runner->onFailure(inst_meth($reporter, 'reportFailure'));
        $runner->onSkip(inst_meth($reporter, 'reportSkip'));
        $runner->onSuccess(inst_meth($reporter, 'reportSuccess'));
        $runner->onPass(inst_meth($reporter, 'reportPass'));

        $runner->onRunStart(inst_meth($reporter, 'identifyPackage'));
        $runner->onRunStart(inst_meth($reporter, 'startTiming'));

        // This is done here to ensure the coverage report only includes the minimum support code as possible.
        if($options->coverage !== CoverageLevel::none) {
            $srcLoader = new Coverage\Loader(
                $options->sourceFolders,
                class_meth(self::class, 'fileOutliner')
            );
            $driver = new Coverage\Driver\FbCoverageDriver();
            $coverage = new Coverage\Processor(
                $srcLoader,
                $driver,
            );
            $runner->onRunStart(inst_meth($driver, 'start'));
            $runner->onRunEnd(inst_meth($driver, 'stop'));
        }

        $runner->onRunEnd(inst_meth($reporter, 'displaySummary'));

        $runner->onUncaughtException(inst_meth($reporter, 'reportUntestedException'));

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
