<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Assertion\CallableAssertion;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Tests\Fixtures\TestException;

<<TestSuite>>
class CallableAssertionTest
{
    use AssertionTest;

    const string message = 'This is the test exception message.';
    const string subMessage = 'is the test';
    const string notSubMessage = 'not in the test message';
    const string exceptionClass = TestException::class;

    private function buildNonThrowingAssertion() : CallableAssertion
    {
        return new CallableAssertion(
            () ==> {},
            $this->failListeners(),
            $this->successListeners(),
        );
    }

    private function buildThrowingAssertion() : CallableAssertion
    {
        return new CallableAssertion(
            () ==> {throw new TestException(self::message);},
            $this->failListeners(),
            $this->successListeners(),
        );
    }

    <<Test>>
    public function expectedMissingException(Assert $assert) : void
    {
        $this->buildNonThrowingAssertion()->willNotThrow();

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function unexpectedException(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willNotThrow();

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function expectedException(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrow();

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectedExceptionWithMessage(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowMessage(self::message);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectedExceptionWithMessageContaining(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowMessageContaining(self::message);
        $this->buildThrowingAssertion()->willThrowMessageContaining(self::subMessage);

        $assert->int($this->successCount)->eq(2);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectedExceptionClass(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClass(self::exceptionClass);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectedExceptionClassWithMessage(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClassWithMessage(self::exceptionClass, self::message);

        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function expectedExceptionClassWithMessageContaining(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClassWithMessageContaining(self::exceptionClass, self::message);
        $this->buildThrowingAssertion()->willThrowClassWithMessageContaining(self::exceptionClass, self::subMessage);

        $assert->int($this->successCount)->eq(2);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function missingException(Assert $assert) : void
    {
        $this->buildNonThrowingAssertion()->willThrow();

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function missingExceptionWithMessage(Assert $assert) : void
    {
        $this->buildNonThrowingAssertion()->willThrowMessage(self::message);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function missingExceptionClass(Assert $assert) : void
    {
        $this->buildNonThrowingAssertion()->willThrowClass(self::class);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function missingExceptionClassWithMessage(Assert $assert) : void
    {
        $this->buildNonThrowingAssertion()->willThrowClassWithMessage(self::class, self::message);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function missingExceptionClassWithMessageContaining(Assert $assert) : void
    {
        $this->buildNonThrowingAssertion()->willThrowClassWithMessageContaining(self::class, self::message);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function wrongMessage(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowMessage(self::notSubMessage);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function messageDoesNotContain(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowMessageContaining(self::notSubMessage);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function wrongClass(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClass(self::class);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function wrongClassRightMessage(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClassWithMessage(self::class, self::message);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function wrongClassMessageDoesContain(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClassWithMessageContaining(self::class, self::message);
        $this->buildThrowingAssertion()->willThrowClassWithMessageContaining(self::class, self::subMessage);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(2);
    }

    <<Test>>
    public function rightClassWrongMessage(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClassWithMessage(self::exceptionClass, self::subMessage);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }

    <<Test>>
    public function rightClassMessageDoesNotContain(Assert $assert) : void
    {
        $this->buildThrowingAssertion()->willThrowClassWithMessageContaining(self::exceptionClass, self::notSubMessage);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
    }
}
