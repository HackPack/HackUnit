<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

type MalformedSuiteListener = (function(MalformedSuite):void);

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

    public function line() : ?int
    {
        return $this->item['line'];
    }

    public function method() : ?string
    {
        return $this->item['function'];
    }

    public function className() : ?string
    {
        return $this->item['class'];
    }

    public function fileName() : ?string
    {
        return $this->item['file'];
    }

    public function message() : string
    {
        return $this->reason;
    }
}
