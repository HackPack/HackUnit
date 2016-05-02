<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface KeyedContainerAssertion<Tkey, Tval> extends Assertion {
  public function containsKey(Tkey $expected): void;
  public function contains(Tkey $key, Tval $val): void;
  public function containsAll(KeyedContainer<Tkey, Tval> $expected): void;
  public function containsAny(KeyedContainer<Tkey, Tval> $expected): void;
}
