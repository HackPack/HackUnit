<?hh // strict

namespace HackPack\HackUnit\Event;

final class MalformedSuite
{
    public function __construct(private \ReflectionMethod $methodMirror, public string $reason)
    {

    }
}
