<?hh //strict
namespace HackPack\HackUnit\Runner;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Runner\Loading\LoaderInterface;
use HackPack\HackUnit\UI\ReporterInterface;
use HackPack\Hacktions\EventEmitter;

class Runner<TLoader as LoaderInterface>
{
    use EventEmitter;

    public function __construct(
        protected ReporterInterface $reporter,
        protected Options $options,
        protected TLoader $loader,
        protected TestResult $result,
    )
    {
    }

    public function getLoader(): TLoader
    {
        return $this->loader;
    }

    public function run(): int
    {
        if (is_file($this->options->getHackUnitFile())) {
            /* HH_FIXME[1002] */
            include_once($this->options->getHackUnitFile());
        }

        $this->reporter->showInfo();

        // Register feedback
        $this->result->startTimer();
        $this->result->on('testFailed', inst_meth($this->reporter, 'showFailure'));
        $this->result->on('testSkipped', inst_meth($this->reporter, 'showSkipped'));
        $this->result->on('testPassed', inst_meth($this->reporter, 'showSuccess'));

        // Load and run the tests
        $this->loader->loadSuite()->run($this->result);
        $this->result->stopTimer();
        $this->reporter->showReport($this->result);
        return $this->result->getExitCode();
    }
}
