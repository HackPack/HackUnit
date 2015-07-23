<?hh // strict

namespace HackPack\HackUnit\Tests\Mocks\Test;

use \HackPack\HackUnit\Contract\Test\TestCase;

class Suite implements \HackPack\HackUnit\Contract\Test\Suite
{
    public function __construct(public \ReflectionClass $mirror)
    {}
    public function registerSuiteSetup(\ReflectionMethod $methodMirror) : void
    {}
    public function registerSuiteTeardown(\ReflectionMethod $methodMirror) : void
    {}
    public function registerTestMethod(\ReflectionMethod $testMethod) : void
    {}
    public function registerTestSetup(\ReflectionMethod $methodMirror) : void
    {}
    public function registerTestTeardown(\ReflectionMethod $methodMirror) : void
    {}
    public function setup() : void
    {}
    public function teardown() : void
    {}
    public function testCases() : Vector<TestCase>
    {return Vector{};}
}
