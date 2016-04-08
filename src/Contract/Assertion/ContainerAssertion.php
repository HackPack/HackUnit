<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface ContainerAssertion<Tval> extends Assertion {

  public function contains(
    Tval $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function containsAll(
    Container<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function containsAny(
    Container<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function containsOnly(
    Container<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void;

  public function isEmpty(): void;
}
