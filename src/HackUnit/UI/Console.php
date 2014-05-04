<?hh //strict
namespace HackUnit\UI;

use HackUnit\Runner\Loading\LoaderInterface;
use HackUnit\Runner\Runner;
use HackUnit\Runner\Options;

class Console<TLoader as LoaderInterface>
{
    protected Runner<LoaderInterface> $runner;

    public function __construct(protected Options $options)
    {
        $factory = class_meth('\HackUnit\Runner\Loading\StandardLoader', 'create');
        $this->options = $this->options = $options;
        $this->runner = new Runner($this->options, $factory);
    }

    public function run(): void
    {
        if (is_file($this->options->getBootstrap())) {
            // UNSAFE
            include_once($this->options->getBootstrap());
        }
        $result = $this->runner->run();
        $ui = new Text($result);
        print $ui->getReport();
        // UNSAFE
        exit($result->getExitCode());
    }
}
