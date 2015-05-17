<?hh // strict

namespace HackPack\HackUnit\Event;

final class MalformedSuite
{
    public static function badMethod(\ReflectionMethod $methodMirror, string $reason) : this
    {
        return new static();
    }

    public static function badClass(\ReflectionClass $classMirror, string $reason) : this
    {
        return new static();
    }

    public function __construct()
    {

    }
}
