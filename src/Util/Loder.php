<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Test\Suite;

final class Loader
{
    public function __construct(
        private Set<string> $includes,
        private Set<string> $excludes,
    )
    {
    }

    public function testSuites() : Vector<Suite>
    {
        $scanner = new \HackPack\Scanner\ClassScanner(
            $this->includes,
            $this->excludes
        );

        $out = Vector{};

        foreach($scanner->mapClassToFile() as $className => $fileName) {
            $this->load($fileName);
            try {
                $classMirror = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                // Unable to load the file, or the map was wrong?
                // Should we warn the user?
                continue;
            }

            $suite = $this->buildSuite($classMirror);
            if($suite !== null) {
                $out->add($suite);
            }
        }

        return $out;
    }

    private function buildSuite(\ReflectionClass $classMirror) : ?Suite
    {
        $constructor = $classMirror->getConstructor();
        if( $constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            // Test suites must never require params to be constructed.
            // Inform the user somehow?
            return null;
        }

        $className = $classMirror->getName();
        $classFile = $classMirror->getFileName();
        if( ! is_string($className) || ! is_string($classFile)) {
            // Reflector unable to figure the class name/file
            // This indicates something is wrong, so consertavitely do not
            // load this suite
            // Inform the user?
            return null;
        }

        $instance = $classMirror->newInstance();
        $suite = new Suite($classFile, $className);
        foreach($classMirror->getMethods() as $methodMirror) {
            if(
                $methodMirror->isAbstract() ||
                $methodMirror->isConstructor() ||
                $methodMirror->isDestructor() ||
                $methodMirror->isStatic()
            ) {
                // Must be normal instance method
                continue;
            }

            $invocation = () ==> {
                $methodMirror->invoke($instance);
            };

            if($this->isSuiteSetup($methodMirror)) {
                $suite->registerSuiteSetup($invocation);
            }

            if($this->isSuiteTeardown($methodMirror)) {
                $suite->registerSuiteTeardown($invocation);
            }

            if($this->isTestSetup($methodMirror)) {
                $suite->registerTestSetup($invocation);
            }

            if($this->isTestTeardown($methodMirror)) {
                $suite->registerTestTeardown($invocation);
            }

            if($this->isTest($methodMirror)) {
                $suite->registerTest((AssertionBuilder $builder) ==> {
                    $methodMirror->invoke($instance, $builder);
                });
            }
        }

        return $suite;
    }

    private function isSuiteSetup(\ReflectionMethod $methodMirror) : bool
    {
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            // Inform the user?
            return false;
        }
        $setup = $methodMirror->getAttribute('setup');

        // Need to mark with <<setup('suite')>>
        return
            is_array($setup) &&
            array_search('suite', $setup) !== false;

    }

    private function isTestSetup(\ReflectionMethod $methodMirror) : bool
    {
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            // Inform the user?
            return false;
        }
        $setup = $methodMirror->getAttribute('setup');
        if(! is_array($setup)) {
            // Need to mark with <<setup>>
            return false;
        }

        // Parameter is optional for tests
        return
            count($setup) === 0 ||
            array_search('test', $setup) !== false;
    }

    private function isSuiteTeardown(\ReflectionMethod $methodMirror) : bool
    {
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            // Inform the user?
            return false;
        }
        $teardown = $methodMirror->getAttribute('teardown');

        // Need to mark with <<teardown('suite')>>
        return
            is_array($teardown) &&
            array_search('suite', $teardown) !== false;

    }

    private function isTestTeardown(\ReflectionMethod $methodMirror) : bool
    {
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            // Inform the user?
            return false;
        }
        $teardown = $methodMirror->getAttribute('teardown');
        if(! is_array($teardown)) {
            // Need to mark with <<teardown>>
            return false;
        }

        // Parameter is optional for tests
        return
            count($teardown) === 0 ||
            array_search('test', $teardown) !== false;
    }

    private function isTest(\ReflectionMethod $methodMirror) : bool
    {
        // Look for <<test>> attribute
        if($methodMirror->getAttribute('test') === null) {
            return false;
        }

        // Ensure method takes an AssertionBuilder as the only parameter
        $params = new Vector($methodMirror->getParameters());

        if($params->count() !== 1) {
            // Inform the user?
            return false;
        }

        if($params->at(0)->getTypeText() !== AssertionBuilder::class) {
            // Inform the user?
            return false;
        }

        return true;
    }

    private function load(string $fileName) : void
    {
        // Is there a better way of dynamically including files?
        /* HH_FIXME[1002] */
        require_once($fileName);
    }
}
