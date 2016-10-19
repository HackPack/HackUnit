<?hh // strict

namespace HackPack\HackUnit\Contract;

use HackPack\HackUnit\Util\TraceItem;

interface Assert {
  public function bool(bool $context): Assertion\BoolAssertion;
  public function int(int $context): Assertion\NumericAssertion<int>;
  public function float(float $context): Assertion\NumericAssertion<float>;
  public function string(string $context): Assertion\StringAssertion;
  public function whenCalled(
    (function(): void) $context,
  ): Assertion\CallableAssertion;
  public function mixed(mixed $context): Assertion\MixedAssertion;
  public function container<T>(
    Container<T> $context,
  ): Assertion\ContainerAssertion<T>;
  public function keyedContainer<Tkey, Tval>(
    KeyedContainer<Tkey, Tval> $context,
  ): Assertion\KeyedContainerAssertion<Tkey, Tval>;
  public function skip(string $reason, ?TraceItem $traceItem = null): void;
  public function fail(string $reason): void;
}
