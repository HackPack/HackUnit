<?hh //strict
namespace HackUnit\Core;

class ExpectationTest extends TestCase
{
    public function test_getContext_returns_value_being_tested(): void
    {
        $expectation = new Expectation(1 + 1);
        $this->expect($expectation->getContext())->toEqual(2);
    }

    public function test_toEqual_does_not_throw_exception_when_true(): void
    {
        $expectation = new Expectation(1 + 1);
        $expected = true;
        $expectation->toEqual(2);
    }

    public function test_toEqual_throws_ExpectationException_if_fails(): void
    {
        $expectation = new Expectation(1 + 1);
        $exception = new ExpectationException();
        try {
            $expectation->toEqual(3);
        } catch (ExpectationException $e) {
            $exception = $e;
        }
        $this->expect($exception->getMessage())->toEqual('Expected 3, got 2');
    }
}
