<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Assertion\MixedAssertion;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\Failure;

<<TestSuite>>
class MixedAssertionTest
{
    private static Vector<mixed> $truish = Vector{
        '1',
        '-1',
        '-1.1',
        '0.0',
        Vector{''},
        [''],
        'a',
        1,
        -1,
        1.1,
        -0.1,
    };

    private static Vector<mixed> $falsish = Vector{
        '0',
        Vector{},
        [],
        '',
        0,
        0.0,
    };

    private Vector<Failure> $failEvents = Vector{};
    private int $successCount = 0;

    <<Setup>>
    public function clearCounts() : void
    {
        $this->failEvents->clear();
        $this->successCount = 0;
    }

    private function buildAssertion(mixed $context) : MixedAssertion
    {
        return new MixedAssertion(
            $context,
            Vector{$e ==> {$this->failEvents->add($e);}},
            Vector{() ==> {$this->successCount++;}},
        );
    }

    <<Test>>
    public function looseComparisonToTrue(Assert $assert) : void
    {
         $assertion = $this->buildAssertion(true);
         $expectedSuccess = 0;
         foreach(self::$truish as $truish) {
             $expectedSuccess++;
             $assertion->looselyEquals($truish);
             $assert->int($this->successCount)->eq($expectedSuccess);
             $assert->int($this->failEvents->count())->eq(0);
         }
    }

    <<Test>>
    public function strictComparisonToTrue(Assert $assert) : void
    {
         $assertion = $this->buildAssertion(true);
         $expectedFailures = 0;
         foreach(self::$truish as $truish) {
             $expectedFailures++;
             $assertion->identicalTo($truish);
             $assert->int($this->failEvents->count())->eq($expectedFailures);
             $assert->int($this->successCount)->eq(0);
         }
    }

    <<Test>>
    public function looseComparisonToFalse(Assert $assert) : void
    {
         $assertion = $this->buildAssertion(false);
         $expectedSuccess = 0;
         foreach(self::$falsish as $falsish) {
             $expectedSuccess++;
             $assertion->looselyEquals($falsish);
             $assert->int($this->successCount)->eq($expectedSuccess);
             $assert->int($this->failEvents->count())->eq(0);
         }
    }

    <<Test>>
    public function strictComparisonToFalse(Assert $assert) : void
    {
         $assertion = $this->buildAssertion(false);
         $expectedFailures = 0;
         foreach(self::$falsish as $falsish) {
             $expectedFailures++;
             $assertion->identicalTo($falsish);
             $assert->int($this->failEvents->count())->eq($expectedFailures);
             $assert->int($this->successCount)->eq(0);
         }
    }
}
