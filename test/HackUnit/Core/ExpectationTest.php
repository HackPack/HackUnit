<?hh //strict
namespace HackUnit\Core;

class ExpectationTest extends TestCase
{
    public function test_getContext_returns_value_being_tested(): void
    {
        $expectation = new Expectation(1 + 1);
        $expected = 2;
        $actual = $expectation->getContext();
        if ($expected != $actual) {
            throw new \Exception("Expected $expected, got $actual");
        }
    }

    public function test_toEqual_returns_true_when_true(): void
    {
        $expectation = new Expectation(1 + 1);
        $expected = true;
        $actual = $expectation->toEqual(2);
        if ($expected != $actual) {
            throw new \Exception("Expected $expected, got $actual");
        }
    }
}
