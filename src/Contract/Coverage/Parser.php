<?hh // strict

namespace HackPack\HackUnit\Contract\Coverage;

interface Parser
{
    public function fileNames() : Set<string>;
    public function executableLinesFor(string $fileName) : Set<int>;
}
