<?hh // strict

namespace HackPack\HackUnit\Coverage;

use HackPack\HackUnit\Contract\Coverage\CoverageReportItem;
use HackPack\HackUnit\Contract\Coverage\Loader;
use HackPack\HackUnit\Contract\Coverage\Driver;

class Processor implements \HackPack\HackUnit\Contract\Coverage\Processor
{
    public function __construct(
        private Loader $loader,
        private Driver $driver,
    )
    {
    }

    public function getReport() : Vector<CoverageReportItem>
    {
        $raw = $this->driver->data();
        if(count($raw) === 0) {
             return Vector{};
        }

        $filesToCover = $this->loader->fileNames();
        return $raw
            ->filterWithKey(($fileName, $lines) ==> $filesToCover->contains($fileName))
            ->mapWithKey(($fileName, $lines) ==> $this->determineCoverage(
                $fileName,
                $lines
            ))->toVector();
    }

    private function determineCoverage(string $fileName, Set<int> $linesExecuted) : CoverageReportItem
    {
        $executableLines = $this->loader->executableLinesFor($fileName);
        if($executableLines->isEmpty()) {
            return shape(
                'file' => $fileName,
                'fraction covered' => 0,
                'uncovered lines' => Set{},
            );
        }

        $lineCount = $executableLines->count();
        $uncovered = $executableLines->removeAll($linesExecuted);
        $coveredCount = $lineCount - $uncovered->count();

        return shape(
            'file' => $fileName,
            'fraction covered' => (int)round(100 * ($coveredCount / $lineCount)),
            'uncovered lines' => $uncovered,
        );
    }
}
