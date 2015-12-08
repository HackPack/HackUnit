<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Assertion\StringAssertion;

class StringAssertionTest
{
    use AssertionTest;

    const string test = 'test string';
    const string superString = 'not test string';
    const string subString = 'test';
    const int realLen = 11;
    const int lessLen = 8;
    const int moreLen = 12;
    const string matchingPattern = '/t s/';
    const string nonMatchingPattern = '/u/';

    private function makeAssertion() : StringAssertion
    {
        return new StringAssertion(
            self::test,
            $this->failListeners(),
            $this->successListeners(),
        );
    }

    <<Test>>
    public function expectSameToPass(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->is(self::test);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectSameToFail(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->is(self::superString);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function expectDifferentToPass(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->not()->is(self::superString);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectDifferentToFail(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->not()->is(self::test);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function expectContainsToPass(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->contains(self::subString);
        $a->contains(self::test);

        $assert->int($this->successCount)->eq(2);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectContainsToFail(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->contains(self::superString);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function expectContainedByToPass(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->containedBy(self::superString);
        $a->containedBy(self::test);

        $assert->int($this->successCount)->eq(2);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectContainedByToFail(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->containedBy(self::subString);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function expectMatchesToPass(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->matches(self::matchingPattern);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectMatchesToFail(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->matches(self::nonMatchingPattern);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function expectHasLengthToPass(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->hasLength(self::realLen);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectHasLengthToFail(Assert $assert) : void
    {
        $a = $this->makeAssertion();
        $a->hasLength(self::lessLen);
        $a->hasLength(self::moreLen);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(2);
    }
}
