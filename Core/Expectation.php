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

    public function toBeInstanceOf(string $expectedClassName): void
    {
      if (!is_object($this->context)) {
          $type = gettype($this->context);
          $message = sprintf("Got a %s value, expected to get an instance of '%s'.", $type, $expectedClassName);
          throw new ExpectationException($message);
      }

      if (!is_a($this->context, $expectedClassName)) {
        $instanceClassName = get_class($this->context);
        $message = sprintf("Got an instance of '%s', expected to get an instance of '%s'.", $expectedClassName, $instanceClassName);
        throw new ExpectationException($message);
      }
    }
}
