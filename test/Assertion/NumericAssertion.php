<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Assertion\NumericAssertion;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Tests\Event\FailureEventTraceAssertions;

<<TestSuite>>
class NumericAssertionTest
{
    use AssertionTest;
    use FailureEventTraceAssertions;

    private function makeAssertion(int $context) : NumericAssertion<int>
    {
        return new NumericAssertion(
            $context,
            $this->failListeners(),
            $this->successListeners(),
        );
    }

    <<Test>>
    public function numbersAreEqualAndExpected(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->eq(1);

        $assert->int($this->successCount)->eq(1);
        $assert->bool($this->failEvents->isEmpty())->is(true);
    }

    <<Test>>
    public function numbersAreUnequalAndExpected(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->not()->eq(0);

        $assert->int($this->successCount)->eq(1);
        $assert->bool($this->failEvents->isEmpty())->is(true);
    }

    <<Test>>
    public function numbersAreEqualAndUnexpected(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->eq(0);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);

        $this->testFailureEvent(
            $assert,
            $this->failEvents->at(0),
            $line,
            __FUNCTION__,
            __CLASS__,
            __FILE__,
        );

    }

    <<Test>>
    public function numbersAreUnequalAndUnexpected(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->not()->eq(1);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);

        $this->testFailureEvent(
            $assert,
            $this->failEvents->at(0),
            $line,
            __FUNCTION__,
            __CLASS__,
            __FILE__,
        );
    }
}
