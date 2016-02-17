<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

final class Skip
{
    public static function fromCallStack(string $message) : this
    {
        return new static(
            $message,
            Trace::findTestMethod(),
        );
    }

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

