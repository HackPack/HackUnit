<?hh // strict

namespace HackPack\HackUnit\Contract\Coverage;

type CoverageReportItem = shape(
    'file' => string,
    'fraction covered' => int,
    'uncovered lines' => Set<int>,
);

interface Processor
{
    public function getReport() : Vector<CoverageReportItem>;
}
