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
        $equals = $this->getContext() == $comparison;
        if (!$equals) {
            $message = sprintf(
                "Actual:\n%sExpected:\n%s",
                $this->captureVarDump($this->getContext()),
                $this->captureVarDump($comparison),
            );
            throw new ExpectationException($message);
        }
    }

    public function toMatch(string $pattern): void
    {
        $match = preg_match($pattern, $this->context);
        if (! $match) {
            $message = sprintf('Expected %s to match pattern "%s"', $this->getContext(), $pattern);
            throw new ExpectationException($message);
        }
    }

    private function captureVarDump(mixed $var) : string
    {
        ob_start();
        trim(implode("\n    ", explode("\n", var_dump($var))));
        return '    ' . ob_get_clean();
    }
}
