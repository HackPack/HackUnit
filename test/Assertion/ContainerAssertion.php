<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Assertion\ContainerAssertion;
use HackPack\HackUnit\Tests\TraceItemTest;

class ContainerAssertionTest {

  use AssertionTest;

  private function makeAssertion<T>(
    Container<T> $actual,
  ): ContainerAssertion<T> {
    return new ContainerAssertion(
      $actual,
      $this->failListeners(),
      $this->successListeners(),
    );
  }

  <<Test>>
  public function doesContain(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->contains('b');
    $assertion->contains('a');

    $assert->int($this->successCount)->eq(2);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {1, 3});

    $assertion->contains(2, ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function emptyDoesNotContain(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {});

    $assertion->not()->contains('c');

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContain(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->not()->contains('c');

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->not()->contains('a', ($a, $b) ==> false);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContain(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->contains(1);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function emptyFailsToContain(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {});

    $assertion->contains(1);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
    $assert->string($this->failEvents->at(0)->getMessage())
      ->is('The Container is empty.');
  }

  <<Test>>
  public function failsToContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->contains('a', ($a, $b) ==> false);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotContain(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->not()->contains('b');

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->not()->contains('c', ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function doesContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsAll(Vector {'a', 'c'});

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsAll(Vector {'a', 'c', 'e'}, ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->not()->containsAll(Vector {'c', 'a'});

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAll(Vector {'a', 'c'}, ($a, $b) ==> false);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->containsAll(Vector {'c', 'a'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->containsAll(Vector {'b', 'a'}, ($a, $b) ==> false);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAll(Vector {'a', 'b'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAll(Vector {'a', 'd'}, ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function doesContainAny(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsAny(Vector {'a', 'd'});

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesContainAnyCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsAny(Vector {'d'}, ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainAny(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAny(Vector {'f', 'd'});

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function emptyDoesNotContainAny(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {});

    $assertion->not()->containsAny(Vector {'f', 'd'});

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainAnyCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAny(Vector {'a', 'd'}, ($a, $b) ==> false);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContainAny(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsAny(Vector {'f', 'd'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function emptyFailsToContainAny(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {});

    $assertion->containsAny(Vector {'f', 'd'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
    $assert->string($this->failEvents->at(0)->getMessage())
      ->is('The Container is empty.');
  }

  <<Test>>
  public function failsToNotContainAny(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAny(Vector {'f', 'b'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotContainAnyCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not()->containsAny(Vector {'d', 'f'}, ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function doesContainOnly(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsOnly(Vector {'b', 'c', 'a'});

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesContainOnlyCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsOnly(Vector {'d', 'c', 'a'}, ($a, $b) ==> true);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainOnly(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->not();
    $assertion->containsOnly(Vector {});
    $assertion->containsOnly(Vector {'g', 'a'});
    $assertion->containsOnly(Vector {'g', 'c', 'a'});
    $assertion->containsOnly(Vector {'b', 'g', 'c', 'a'});

    $assert->int($this->successCount)->eq(4);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainOnlyCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});
    $assertion->not()
      ->containsOnly(Vector {'a', 'b', 'c'}, ($a, $b) ==> false);

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function containsTooMany(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b', 'c'});

    $assertion->containsOnly(Vector {'g', 'a'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
    $assert->string($this->failEvents->at(0)->getMessage())
      ->is('Container contains more elements than expected.');
  }

  <<Test>>
  public function containsTooFew(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a'});

    $assertion->containsOnly(Vector {'g', 'a'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
    $assert->string($this->failEvents->at(0)->getMessage())
      ->is('Container contains fewer elements than expected.');
  }

  <<Test>>
  public function containsWrongElement(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->containsOnly(Vector {'c', 'a'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
    $assert->string($this->failEvents->at(0)->getMessage())
      ->is('Container expected to contain '.var_export('c', true));
  }

  <<Test>>
  public function failsToNotContainOnly(Assert $assert): void {
    $assertion = $this->makeAssertion(Vector {'a', 'b'});

    $assertion->not()->containsOnly(Vector {'b', 'a'});

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function isEmpty(Assert $assert): void {
    $assertion = $this->makeAssertion([]);

    $assertion->isEmpty();

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function isNotEmpty(Assert $assert): void {
    $assertion = $this->makeAssertion([1]);

    $assertion->not()->isEmpty();

    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToBeEmpty(Assert $assert): void {
    $assertion = $this->makeAssertion([1]);

    $assertion->isEmpty();

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotBeEmpty(Assert $assert): void {
    $assertion = $this->makeAssertion([]);

    $assertion->not()->isEmpty();

    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }
}
