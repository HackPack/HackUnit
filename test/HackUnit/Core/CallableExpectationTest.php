<?hh //strict
namespace HackUnit\Core;

class CallableExpectationTest extends TestCase
{
    protected ?(function(): void) $callable;

    <<Override>> public function setUp(): void
    {
        $this->callable = $fun = () ==> {throw new ExpectationException('unexpected!');};
    }

    public function test_toThrow_does_nothing_if_exception_thrown(): void
    {
        if ($this->callable) {
            $expectation = new CallableExpectation($this->callable);
            $expectation->toThrow('\HackUnit\Core\ExpectationException');
        }
    }

    public function test_toThrow_throws_exception_if_wrong_exception_type(): void
    {
        $exception = new \Exception();
        if ($this->callable) {
            $expectation = new CallableExpectation($this->callable);
            try {
                $expectation->toThrow('\RuntimeException');
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        $this->expect($exception->getMessage())->toEqual('Expected exception of type \RuntimeException to be thrown');
    }
}
