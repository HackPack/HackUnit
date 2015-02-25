<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Runner\Loading\LoaderInterface;
use HackPack\HackUnit\Runner\Runner;
use HackPack\HackUnit\Runner\Options;

class Console<TLoader as LoaderInterface>
{
    protected Runner<LoaderInterface> $runner;

    const string VERSION = "0.3.2";

    public function __construct(protected Options $options, LoaderInterface $loader)
    {
        $this->runner = new Runner($options, $loader);
    }

    public function run(): void
    {
        if (is_file($this->options->getHackUnitFile())) {
            /* HH_FIXME[1002] */
            include_once($this->options->getHackUnitFile());
        }
        $this->printInfo();
        $ui = $this->getTextUI();
        $result = $this->runner->run();
        $ui->printReport($result);

        exit($result->getExitCode());
    }

    protected function printInfo(): void
    {
        printf(
            "HackUnit %s by HackPack.\n\n",
            static::VERSION
        );
    }

    protected function getTextUI(): TextReporterInterface
    {
        $ui = new Text();
        $this->runner->on('testFailed', (...) ==> $ui->printFeedback("\033[41;37mF\033[0m"));
        $this->runner->on('testSkipped', (...) ==> $ui->printFeedback('S'));
        $this->runner->on('testPassed', (...) ==> $ui->printFeedback('.'));
        $ui->enableColor();
        return $ui;
    }
}
