<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface BoolAssertion
{
    public function is(bool $expected) : void;
}
