<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\Skip;

interface Suite
{
    public function registerSuiteSetup(\ReflectionMethod $methodMirror) : void;
    public function registerSuiteTeardown(\ReflectionMethod $methodMirror) : void;
    public function registerTestMethod(\ReflectionMethod $testMethod) : void;
    public function registerTestSetup(\ReflectionMethod $methodMirror) : void;
    public function registerTestTeardown(\ReflectionMethod $methodMirror) : void;
    public function setup() : Awaitable<void>;
    public function teardown() : Awaitable<void>;
    public function testCases() : Vector<TestCase>;
}
