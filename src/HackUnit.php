<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit
{
    public static function fromCli(string $projectDir) : void
    {
        $srcLoader = new Coverage\Loader(
            self::listSourceFiles($projectDir),
            class_meth(self::class, 'fileOutliner')
        );
        $driver = new Coverage\Driver\FbCoverageDriver();
        $coverage = new Coverage\Coverage(
            $srcLoader,
            $driver,
        );
        $driver->start();
        $clio = \kilahm\Clio\Clio::fromCli();
        $options = Util\Options::fromCli($clio);
        $driver->stop();
        var_dump($coverage->getReport());
        exit();

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
        $runner->onRunStart(() ==> {
            $reporter->identifyPackage();
            $reporter->startTiming();
        });
        $runner->onRunEnd(inst_meth($reporter, 'displaySummary'));
        $runner->onUncaughtException(inst_meth($reporter, 'reportUntestedException'));

        $runner->run($loader->testSuites());
    }

    public static function listSourceFiles(string $projectDir) : Set<string>
    {
        $projectDir = realpath($projectDir);
        var_dump($projectDir);
        if(! is_string($projectDir)) {
            throw new \RuntimeException('Provided project directory does not exist.');
        }

        $rawFileList = shell_exec('hh_client --retry-if-init true --list-modes ' . escapeshellarg($projectDir));
        if(! is_string($rawFileList)) {
            throw new \RuntimeException('Unable to scan project directory with hh_client.');
        }

        return (new Set(explode(PHP_EOL, $rawFileList)))
            ->filter($line ==>
                // Filter out the composer vendor directory
                strpos($line, $projectDir . '/vendor') === false &&
                // Filter out the test directory
                strpos($line, $projectDir . '/test') === false &&
                // Never cover hhi files
                substr($line, -4) !== '.hhi' &&
                // Trailing newline causes an empty line after explode
                $line !== ''
            )
            // git rid of the hack mode portion of the line
            ->map($line ==> preg_replace('/^(?:php|decl|partial|strict)\s*(.*)/', '$1', $line))
            ;
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
