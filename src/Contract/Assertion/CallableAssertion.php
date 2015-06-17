<?hh // strict

namespace HackPack\HackUnit\Contract\Assertion;

interface CallableAssertion extends Assertion
{
    public function willThrow() : void;
    public function willThrowClass(string $className) : void;
    public function willThrowMessage(string $message) : void;
    public function willThrowMessageContaining(string $needle) : void;
    public function willThrowClassWithMessage(string $className, string $message) : void;
    public function willThrowClassWithMessageContaining(string $className, string $needle) : void;
    public function willNotThrow() : void;
}
