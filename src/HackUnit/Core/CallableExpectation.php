<?hh //strict
namespace HackUnit\Core;

class CallableExpectation
{
    public function __construct(protected (function(): void) $context)
    {
    }

    public function toThrow(string $exceptionType): void
    {
        $exception = null;
        try {
            $fun = $this->context;
            $fun();
        } catch (\Exception $e) {
            $exception = $e;
        }
        if (!is_a($exception, $exceptionType)) {
            throw new ExpectationException("Expected exception of type $exceptionType to be thrown");
        }
    }
}
