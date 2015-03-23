<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Runner\Loading\LoaderInterface;
use HackPack\HackUnit\Runner\Runner;
use HackPack\HackUnit\Runner\Options;
use kilahm\Clio\Clio;
use kilahm\Clio\Format\Style;
use kilahm\Clio\TextColor;

class Console<TLoader as LoaderInterface>
{
    protected Runner<LoaderInterface> $runner;

    const string VERSION = "0.4.0-dev";

    public function __construct(protected Clio $clio, protected Options $options, LoaderInterface $loader)
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

    protected function getTextUI(): void
    {
        $this->runner->on('testFailed', (...) ==> $this->clio->show(
            $this->clio->style('F')->with(Style::error())
        ));
        $this->runner->on('testSkipped', (...) ==> $this->clio->show(
            $this->clio->style('S')->with(Style::warn())
        ));
        $this->runner->on('testPassed', (...) ==> $this->clio->show(
            $this->clio->style('.')->fg(TextColor::green)->render()
        ));
    }
}
