<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;

class Reporter
{
    private bool $colors = false;
    private ?float $starttime = null;
    public function __construct(private \kilahm\Clio\Clio $clio)
    {
    }

    public function startTiming() : void
    {
        $this->starttime = microtime(true);
    }

    public function withColor() : void
    {
        $this->colors = true;
    }

    public function reportFailure(Failure $event) : void
    {
        $this->clio->line('FAILURE!');
    }

    public function reportSkip(Skip $event) : void
    {
        $this->clio->line('SKIP!');
    }

    public function reportSuccess(Success $event) : void
    {
        $this->clio->line('SUCCESS!');
    }

    public function reportPass() : void
    {
        $this->clio->line('Test Passed!');
    }

    public function reportUntestedException(\Exception $e) : void
    {
        $this->clio->line($e->getMessage());
    }

    public function displaySummary() : void
    {
        if($this->starttime !== null) {
            $start = $this->starttime;
            $report = sprintf('Finished testing in %.2f seconds.', (float)(microtime(true) - $start));
        } else {
            $report = 'Finished testing.';
        }
        $this->clio->line($report);
    }
}
