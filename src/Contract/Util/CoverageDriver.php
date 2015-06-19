<?hh // strict

namespace HackPack\HackUnit\Contract\Util;

interface CoverageDriver
{
    /**
     * Driver should map each file name to a set of line numbers actually executed
     */
    public function data() : Map<string, Set<int>>;
}
