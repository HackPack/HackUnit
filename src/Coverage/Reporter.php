<?hh // strict

namespace HackPack\HackUnit\Coverage;

use HackPack\HackUnit\CoverageLevel;
use HackPack\HackUnit\Contract\Coverage\Processor;

use kilahm\Clio\Clio;

class Reporter
{
    public function __construct(
        private CoverageLevel $level,
        private Processor $processor,
        private Clio $clio,
    )
    {
    }

    public function showReport() : void
    {
        switch($this->level)
        {
        case CoverageLevel::none:
            return;
        case CoverageLevel::summary:
            $this->showSummary();
            break;
        case CoverageLevel::full:
            $this->showSummary();
            break;
        }
    }

    private function showSummary() : void
    {
        $this->clio->line('');
        $raw = Map::fromItems(
            $this->processor
            ->getReport()
            ->map($item ==> Pair{$item['file'], $item})
        );
        $files = $raw->keys()->toArray();
        natsort($files);
        foreach($files as $filename) {
            $item = $raw->at($filename);
            if($item['fraction covered'] === 0 && $item['uncovered lines']->isEmpty()) {
                 // No lines to cover
                continue;
            }
            $this->clio->line(sprintf('%3d%% %s', $item['fraction covered'], $item['file']));
        }
    }
}
