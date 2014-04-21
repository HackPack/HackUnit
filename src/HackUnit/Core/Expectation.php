<?hh //strict
namespace HackUnit\Core;

class Expectation<T>
{
    public function __construct(protected T $context)
    {
    }

    public function getContext(): T
    {
        return $this->context;
    }

    public function toEqual(T $comparison): bool
    {
        return $this->getContext() == $comparison;
    }
}
