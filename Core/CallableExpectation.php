<?hh //strict
namespace HackPack\HackUnit\Core;

class CallableExpectation
{
    public function __construct(protected (function(): void) $context)
    {
    }

    public function toThrow(string $exceptionType): void
    {
        try {
            $fun = $this->context;
            $fun();
        } catch (\Exception $e) {
            if (!is_a($e, $exceptionType)) {
                $message = sprintf("%s was thrown in %s (%d).\n%s was expected.", get_class($e), $e->getFile(), $e->getLine(), $exceptionType);
                throw new ExpectationException($message);
            }
            return;
        }
        throw new ExpectationException("No exception was thrown.\n$exceptionType was expected.");
    }

    public function toNotThrow(): void
    {
        $thrown = false;
        try {
            $fun = $this->context;
            $fun();
            $exceptionType = '';
        } catch (\Exception $e) {
            $exceptionType = get_class($e);
            $msg = sprintf("Unexpected exception thrown.\nType: %s\nFile: %s\nMessage: %s", get_class($e), $e->getFile() . ' (' . (string)$e->getLine() . ')', $e->getMessage());
            throw new ExpectationException($msg);
        }
    }
}
