<?hh // strict

namespace HackPack\HackUnit\Contract\Util;

interface SourceLoader
{
    public function fileNames() : Set<string>;
    public function executableLinesFor(string $fileName) : Set<int>;
}
