<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface TraversableAssertion<Tval> extends Assertion {

  public function contains(
    Tval $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function containsAll(
    Traversable<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function containsAny(
    Traversable<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function containsOnly(
    Traversable<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function isEmpty(): void;
}
