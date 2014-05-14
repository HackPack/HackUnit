<?hh //strict
namespace HackPack\HackUnit\Tests\Core;

use HackPack\HackUnit\Core\Expectation;
use HackPack\HackUnit\Core\TestCase;

class ExpectationTest extends TestCase
{
    public function test_getContext_returns_value_being_tested(): void
    {
        $expectation = new Expectation(1 + 1);
        $this->expect($expectation->getContext())->toEqual(2);
    }

    public function test_toEqual_does_not_throw_exception_when_true(): void
    {
        $this->expectCallable(() ==> {
            $expectation = new Expectation(1 + 1);
            $expectation->toEqual(2);
        })->toNotThrow();
    }

    public function test_toEqual_throws_ExpectationException_if_fails(): void
    {
        $this->expectCallable(() ==> {
            $expectation = new Expectation(1 + 1);
            $expectation->toEqual(3);
        })->toThrow('\HackPack\HackUnit\Core\ExpectationException');
    }
}
