<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface MixedAssertion extends Assertion {
  public function not(): this;
  public function isNull(): void;
  public function isBool(): void;
  public function isInt(): void;
  public function isFloat(): void;
  public function isString(): void;
  public function isArray(): void;
  public function looselyEquals(mixed $expected): void;
  public function identicalTo(mixed $expected): void;
  public function isObject(): void;
  public function isTypeOf(string $className): void;
}
