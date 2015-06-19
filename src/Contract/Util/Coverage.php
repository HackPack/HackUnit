<?hh // strict

namespace HackPack\HackUnit\Contract\Util;

type CoverageReportItem = shape(
    'file' => string,
    'fraction covered' => int,
    'uncovered lines' => Set<int>,
);

interface Coverage
{
    public function getReport() : Vector<CoverageReportItem>;
}
