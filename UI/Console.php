<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Runner\Loading\LoaderInterface;
use HackPack\HackUnit\Runner\Runner;
use HackPack\HackUnit\Runner\Options;

class Console<TLoader as LoaderInterface>
{
    protected Runner<LoaderInterface> $runner;

    public function __construct(protected Options $options)
    {
        $factory = class_meth('\HackPack\HackUnit\Runner\Loading\StandardLoader', 'create');
        $this->options = $this->options = $options;
        $this->runner = new Runner($this->options, $factory);
    }

    public function run(): void
    {
        if (is_file($this->options->getHackUnitFile())) {
            // UNSAFE
            include_once($this->options->getHackUnitFile());
        }
        $result = $this->runner->run();
        $ui = new Text();
        $ui->enableColor();
        print $ui->getReport($result);
        // UNSAFE
        exit($result->getExitCode());
    }
}
