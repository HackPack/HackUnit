<?hh // strict

namespace HackPack\HackUnit\Coverage;

use HackPack\HackUnit\CoverageLevel;
use HackPack\HackUnit\Contract\Coverage\Processor;

use kilahm\Clio\Clio;

class Reporter
{
    private bool $useColor = false;

    public function __construct(
        private CoverageLevel $level,
        private Processor $processor,
        private Clio $clio,
    )
    {
    }

    public function enableColors() : void
    {
        $this->useColor = true;
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
        $msg = 'Building coverage report...';
        if($this->useColor) {
            $msg = $this->clio->style($msg)
                ->with(\kilahm\Clio\Format\Style::info())
                ;
        }
        $this->clio->line($msg);
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
            $this->clio->line(implode(', ',
                $this->lineRanges($item['uncovered lines'])
                ->map($range ==> {
                    if($range[0] === $range[1]) {
                        return sprintf('%d', $range[0]);
                    }
                    return sprintf('%d - %d', $range[0], $range[1]);
                })
            ));
        }
    }

    private function lineRanges(Set<int> $lines) : Vector<(int,int)>
    {
        $lines = $lines->toVector();
        sort($lines);
        $ranges = Vector{};
        $start = $end = $lines->at(0);
        foreach($lines as $line) {
            if($line <= $end + 1) {
                // Looking for a gap
                $end = $line;
                continue;
            }

            // Found a gap.  Start a new range.
            $ranges->add(tuple($start, $end));
            $start = $end = $line;
        }
        // Last one always end a range
        $ranges->add(tuple($start, $end));
        return $ranges;
    }
}
