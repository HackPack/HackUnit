<?hh // strict

namespace HackPack\HackUnit;

final class HackUnit
{
    public static function fromCli() : void
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
        $runner->onRunStart(() ==> {
            $reporter->identifyPackage();
            $reporter->startTiming();
        });
        $runner->onRunEnd(inst_meth($reporter, 'displaySummary'));
        $runner->onUncaughtException(inst_meth($reporter, 'reportUntestedException'));

        $runner->run($loader->testSuites());
    }
}
