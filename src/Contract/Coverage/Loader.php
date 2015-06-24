<?hh // strict

namespace HackPack\HackUnit\Contract\Coverage;

interface Loader
{
    public function fileNames() : Set<string>;
    public function executableLinesFor(string $fileName) : Set<int>;
}
