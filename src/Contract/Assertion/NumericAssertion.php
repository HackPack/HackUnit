<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface NumericAssertion<Tcontext> extends Assertion {
  public function not(): this;
  public function eq(Tcontext $expected): void;
  public function gt(Tcontext $expected): void;
  public function gte(Tcontext $expected): void;
  public function lt(Tcontext $expected): void;
  public function lte(Tcontext $expected): void;
}
