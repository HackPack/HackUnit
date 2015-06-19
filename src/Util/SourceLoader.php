<?hh // strict

namespace HackPack\HackUnit\Util;

class SourceLoader implements \HackPack\HackUnit\Contract\Util\SourceLoader
{
    public function fileNames() : Set<string>
    {
        return Set{};
    }

    public function executableLinesFor(string $file) : Set<int>
    {
        return Set{};
    }
}
