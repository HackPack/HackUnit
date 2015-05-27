<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\Skip;

interface Suite
{
    public function fileName() : string;
    public function className() : string;
    public function registerSuiteTeardown((function():void) $f) : void;
    public function registerTestTeardown((function():void) $f) : void;
    public function registerSuiteSetup((function():void) $f) : void;
    public function registerTestSetup((function():void) $f) : void;
    public function registerTest((function(AssertionBuilder):void) $test, \ReflectionMethod $testMethod, bool $skip) : void;
    public function registerSkipHandlers(Traversable<(function(Skip):void)> $handlers) : void;
    public function setup() : void;
    public function teardown() : void;
    public function testSetup() : void;
    public function testTeardown() : void;
    public function cases() : Vector<TestCase>;
}
