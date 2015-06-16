<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface StringAssertion
{
    public function not() : this;
    public function is(string $expected) : this;
    public function hasLength(int $length) : this;
    public function matches(string $pattern) : this;
    public function contains(string $needle) : this;
    public function containedBy(string $haystack) : this;
}
