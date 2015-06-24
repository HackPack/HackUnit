<?hh // strict

namespace HackPack\HackUnit\Coverage\Driver;

class FbCoverageDriver implements \HackPack\HackUnit\Contract\Coverage\Driver
{
    private array<string, array<int, int>> $raw = [];

    public function start() : void
    {
        /* HH_FIXME[4107] */ /* HH_FIXME[2049] */
        fb_enable_code_coverage();
    }

    public function stop() : void
    {
        /* HH_FIXME[4107] */ /* HH_FIXME[2049] */
        $this->raw = fb_disable_code_coverage();
    }

    public function data() : Map<string, Set<int>>
    {
        return (new Map($this->raw))
            ->map($lines ==> new Set(array_keys($lines)));
    }
}
