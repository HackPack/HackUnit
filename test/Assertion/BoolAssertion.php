<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Assertion\BoolAssertion;
use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Event\SuccessListener;

<<TestSuite>>
class BoolAssertionTest
{
    private int $successCount = 0;
    private Vector<Failure> $failEvents = Vector{};

    <<Setup>>
    public function clearCounts() : void
    {
        $this->successCount = 0;
        $this->failEvents->clear();
    }

    private function successListeners() : Vector<SuccessListener>
    {
        return Vector{() ==> {$this->successCount++;}};
    }

    private function failListeners() : Vector<FailureListener>
    {
        return Vector{($e) ==> {$this->failEvents->add($e);}};
    }

    private function makeAssertion(bool $context) : BoolAssertion
    {
        return new BoolAssertion(
            $context,
            $this->failListeners(),
            $this->successListeners(),
        );
    }

    <<Test>>
    public function triggersSuccessWhenBoolsMatch(Assert $assert) : void
    {
        $a = $this->makeAssertion(true);
        $a->is(true);
        $assert->int($this->successCount)->eq(1);
        $assert->int($this->failEvents->count())->eq(0);

        $a = $this->makeAssertion(false);
        $a->is(false);
        $assert->int($this->successCount)->eq(2);
        $assert->int($this->failEvents->count())->eq(0);
    }

    <<Test>>
    public function triggersFailureWhenBoolDoesNotMatch(Assert $assert) : void
    {
        $a = $this->makeAssertion(false);
        $line = __LINE__ + 1;
        $a->is(true);

        $assert->int($this->successCount)->eq(0);
        $assert->int($this->failEvents->count())->eq(1);
        $e = $this->failEvents->at(0);
        $assert->int((int)$e->assertionLine())->eq($line);
        $assert->string((string)$e->testMethod())->is(__FUNCTION__);
        $assert->string((string)$e->testClass())->is(__CLASS__);
        $assert->string((string)$e->testFile())->is(__FILE__);
    }
}
