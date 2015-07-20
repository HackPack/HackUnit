<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Assertion\MixedAssertion;
use HackPack\HackUnit\Contract\Assert;

<<TestSuite>>
class MixedAssertionTest
{
    use AssertionTest;

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

    private static Vector<mixed> $allTypes = Vector{
        true,
        0,
        1.0,
        'a',
        [],
        Vector{},
    };

    private function buildAssertion(mixed $context) : MixedAssertion
    {
        return new MixedAssertion(
            $context,
            $this->failListeners(),
            $this->successListeners(),
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

    <<Test>>
    public function isBool(Assert $assert) : void
    {
        foreach(self::$allTypes as $item) {
            $this->buildAssertion($item)->isBool();
        }
        $assert->int($this->failEvents->count())->eq(self::$allTypes->count() - 1);
        $assert->int($this->successCount)->eq(1);
    }

    <<Test>>
    public function isInt(Assert $assert) : void
    {
        foreach(self::$allTypes as $item) {
            $this->buildAssertion($item)->isInt();
        }
        $assert->int($this->failEvents->count())->eq(self::$allTypes->count() - 1);
        $assert->int($this->successCount)->eq(1);
    }

    <<Test>>
    public function isFloat(Assert $assert) : void
    {
        foreach(self::$allTypes as $item) {
            $this->buildAssertion($item)->isFloat();
        }
        $assert->int($this->failEvents->count())->eq(self::$allTypes->count() - 1);
        $assert->int($this->successCount)->eq(1);
    }

    <<Test>>
    public function isString(Assert $assert) : void
    {
        foreach(self::$allTypes as $item) {
            $this->buildAssertion($item)->isString();
        }
        $assert->int($this->failEvents->count())->eq(self::$allTypes->count() - 1);
        $assert->int($this->successCount)->eq(1);
    }

    <<Test>>
    public function isArray(Assert $assert) : void
    {
        foreach(self::$allTypes as $item) {
            $this->buildAssertion($item)->isArray();
        }
        $assert->int($this->failEvents->count())->eq(self::$allTypes->count() - 1);
        $assert->int($this->successCount)->eq(1);
    }

    <<Test>>
    public function isObject(Assert $assert) : void
    {
        foreach(self::$allTypes as $item) {
            $this->buildAssertion($item)->isObject();
        }
        $assert->int($this->failEvents->count())->eq(self::$allTypes->count() - 1);
        $assert->int($this->successCount)->eq(1);
    }
}
