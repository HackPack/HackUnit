<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Assertion\KeyedContainerAssertion;
use HackPack\HackUnit\Tests\TraceItemTest;

class KeyedContainerAssertionTest {

  use AssertionTest;

  private function makeAssertion<Tkey, Tval>(
    KeyedContainer<Tkey, Tval> $actual,
  ): KeyedContainerAssertion<Tkey, Tval> {
    return new KeyedContainerAssertion(
      $actual,
      $this->failListeners(),
      $this->successListeners(),
    );
  }

  <<Test>>
  public function doesContainKey(Assert $assert): void {
    $this->makeAssertion(['a' => null])->containsKey('a');
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainKey(Assert $assert): void {
    $this->makeAssertion(['a' => null])->not()->containsKey('c');
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContainKey(Assert $assert): void {
    $this->makeAssertion(['a' => null])->containsKey('c');
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function failsToNotContainKey(Assert $assert): void {
    $this->makeAssertion(['a' => null])->not()->containsKey('a');
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function doesContain(Assert $assert): void {
    $this->makeAssertion(['a' => 1, 1 => 'a'])->contains('a', 1);
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContain(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->not()->contains('a', 2);
    $assertion->not()->contains('b', 1);
    $assert->int($this->successCount)->eq(2);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContain(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->contains('a', 2);
    $assertion->contains(2, 'a');
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(2);
  }

  <<Test>>
  public function failsToNotContain(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->not()->contains('a', 1);
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function doesContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->contains(1, 'b', ($key, $a, $b) ==> true);
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->not()->contains('a', 1, ($key, $a, $b) ==> false);
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->contains('a', 1, ($key, $a, $b) ==> false);
    // This one is not a match because the key does not exist
    $assertion->contains(0, 'a', ($key, $a, $b) ==> true);
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(2);
  }

  <<Test>>
  public function failsToNotContainCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a']);
    $assertion->not()->contains(1, 'b', ($key, $a, $b) ==> true);
    $assertion->not()->contains(0, 'a', ($key, $a, $b) ==> true);
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(1);
  }

  <<Test>>
  public function doesContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->containsAll(['a' => 1, 1 => 'a']);
    $assertion->containsAll(['a' => 1, 1 => 'a', 'b' => 1]);
    $assert->int($this->successCount)->eq(2);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->not()->containsAll([0 => 'a']);
    $assertion->not()->containsAll(['a' => 1, 0 => 'a']);
    $assert->int($this->successCount)->eq(2);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->containsAll([0 => 'a']);
    $assertion->containsAll(['a' => 1, 0 => 'a']);
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(2);
  }

  <<Test>>
  public function failsToNotContainAll(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->not()->containsAll([1 => 'a']);
    $assertion->not()->containsAll(['a' => 1, 1 => 'a']);
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(2);
  }

  <<Test>>
  public function doesContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->containsAll(['a' => 1, 1 => 'b'], ($k, $a, $b) ==> true);
    $assert->int($this->successCount)->eq(1);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function doesNotContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->not()->containsAll([0 => 'a'], ($k, $a, $b) ==> true);
    $assertion->not()
      ->containsAll(['a' => 1, 'b' => 1], ($k, $a, $b) ==> false);
    $assert->int($this->successCount)->eq(2);
    $assert->int($this->failEvents->count())->eq(0);
  }

  <<Test>>
  public function failsToContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->containsAll([0 => 'a'], ($k, $a, $b) ==> true);
    $assertion->containsAll(['a' => 1, 0 => 'a'], ($k, $a, $b) ==> false);
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(2);
  }

  <<Test>>
  public function failsToNotContainAllCustom(Assert $assert): void {
    $assertion = $this->makeAssertion(['a' => 1, 1 => 'a', 'b' => 1]);
    $assertion->not()
      ->containsAll(['a' => 1, 'b' => 0], ($k, $a, $b) ==> true);
    $assert->int($this->successCount)->eq(0);
    $assert->int($this->failEvents->count())->eq(1);
  }
}
