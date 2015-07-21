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
    public function expectEqualIsEqual(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->eq(1);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectEqualIsGreater(Assert $assert) : void
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
    public function expectEqualIsLess(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->eq(2);

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
    public function expectGreaterIsEqual(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->gt(1);

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
    public function expectGreaterIsGreater(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->gt(0);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectGreaterIsLess(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->gt(2);

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
    public function expectGreaterOrEqualIsEqual(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->gte(1);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectGreaterOrEqualIsGreater(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->gte(0);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectGreaterOrEqualIsLess(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->gte(2);

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
    public function expectLessIsEqual(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->lt(1);

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
    public function expectLessIsGreater(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->lt(0);

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
    public function expectLessIsLess(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->lt(2);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectLessOrEqualIsEqual(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->lte(1);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectLessOrEqualIsGreater(Assert $assert) : void
    {
        $line = __LINE__ + 2;
        $a = $this->makeAssertion(1);
        $a->lte(0);

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
    public function expectLessOrEqualIsLess(Assert $assert) : void
    {
        $a = $this->makeAssertion(1);
        $a->lte(2);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }
}
