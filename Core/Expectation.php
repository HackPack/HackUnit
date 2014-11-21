<?hh //strict
namespace HackPack\HackUnit\Core;

class Expectation<T>
{
    public function __construct(protected T $context)
    {
    }

    public function getContext(): T
    {
        return $this->context;
    }

    public function toEqual(T $comparison): void
    {
        if ($comparison != $this->context) {
            $expected = $this->captureVarDump($this->context);
            $actual = $this->captureVarDump($comparison);
            $message = sprintf(
                "Unexpected value.\n\nExpected:\n%sActual:\n%s",
                $expected,
                $actual,
            );
            throw new ExpectationException($message);
        }
    }

    public function toBeIdenticalTo(T $comparison): void
    {
        if ($comparison !== $this->context) {
            $expected = $this->captureVarDump($this->context);
            $actual = $this->captureVarDump($comparison);
            $message = sprintf(
                "Values are not identical.\n\nExpected:\n%sActual:\n%s",
                $expected,
                $actual,
            );
            throw new ExpectationException($message);
        }
    }

    public function toMatch(string $pattern): void
    {
        $match = preg_match($pattern, $this->context);
        if (! $match) {
            $message = sprintf(
                'Expected pattern %s to match "%s"',
                $pattern,
                $this->getContext()
            );
            throw new ExpectationException($message);
        }
    }

    private function captureVarDump(mixed $var) : string
    {
        ob_start();
        trim(implode("\n    ", explode("\n", var_dump($var))));
        return '    ' . ob_get_clean();
    }

    public function toBeInstanceOf(string $expectedClassName): void
    {
        if(is_a($this->context, $expectedClassName)){
            return;
        }

        if (is_object($this->context)) {
            $type = get_class($this->context);
        } else {
            $type = gettype($this->context);
        }

        $message = sprintf(
            "Unexpected object type.\n\nExpected: %s\nActual: %s",
            $expectedClassName,
            $type
        );
        throw new ExpectationException($message);
    }

}
