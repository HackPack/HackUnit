<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface BoolAssertion extends Assertion {
  public function is(bool $expected): void;
}
