<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

<<IgnoreCoverage>>
final class MalformedSuite
{
    public static function badMethod(\ReflectionMethod $methodMirror, string $reason) : this
    {
        return new static(
            Trace::buildItem([
                'line' => $methodMirror->getStartLine(),
                'function' => $methodMirror->name,
                'class' => $methodMirror->class,
                'file' => $methodMirror->getFileName(),
            ]),
            $reason,
        );
    }

    public function __construct(
        private TraceItem $item,
        private string $reason,
    )
    {
    }

    public function message() : string
    {
        return $this->reason;
    }

    public function traceItem() : TraceItem
    {
         return $this->item;
    }
}
