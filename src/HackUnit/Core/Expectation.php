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
}
