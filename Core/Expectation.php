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
            $message = sprintf('Expected %s, got %s', $comparison, $this->getContext());
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
}
