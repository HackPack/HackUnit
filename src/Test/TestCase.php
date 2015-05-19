<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Assertion\AssertionBuilder;

class TestCase
{
    public function __construct(
        private Suite $suite,
        private (function(AssertionBuilder):void) $test,
        private \ReflectionMethod $testMethod,
        private bool $skip,
    )
    {
    }

    public function setup() : void
    {
        $this->suite->testSetup();
    }

    public function teardown() : void
    {
        $this->suite->teardown();
    }

    public function run<Tcontext>(AssertionBuilder $builder) : void
    {
        if($this->skip){
            $this->suite->skip($this->testMethod);
            return;
        }
        $builder->setMethod($this->testMethod);
        $t = $this->test;
        $t($builder);
    }
}
