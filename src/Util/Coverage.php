<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Contract\Util\CoverageReportItem;
use HackPack\HackUnit\Contract\Util\SourceLoader;
use HackPack\HackUnit\Contract\Util\CoverageDriver;

class Coverage implements \HackPack\HackUnit\Contract\Util\Coverage
{
    public function __construct(
        private SourceLoader $loader,
        private CoverageDriver $driver,
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
        $coveredCount = $uncovered->count();

        return shape(
            'file' => $fileName,
            'fraction covered' => (int)round($coveredCount / $lineCount),
            'uncovered lines' => $uncovered,
        );
    }
}
