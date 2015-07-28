<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\TraceItem;

class Skip
{
    public function __construct(private string $reason, private TraceItem $callSite)
    {
    }

    public function message() : string
    {
        return $this->reason;
    }

    public function callSite() : TraceItem
    {
        return $this->callSite;
    }
}

