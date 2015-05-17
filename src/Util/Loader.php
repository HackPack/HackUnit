<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Suite;

final class Loader
{

    public function __construct(
        private Set<string> $includes,
        private Set<string> $excludes,
        private Vector<(function(MalformedSuite):void)> $malformedListeners = Vector{},
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
        // Need to mark with <<setup('suite')>>
        $setup = $methodMirror->getAttribute('setup');
        if(! is_array($setup) || array_search('suite', $setup) === false) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(new MalformedSuite(
                $methodMirror,
                'Setup methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isTestSetup(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<setup('test')>> or <<setup>>
        $setup = $methodMirror->getAttribute('setup');
        if(
            ! is_array($setup) ||
            (count($setup) > 0 && array_search('suite', $setup) === false)
        ) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(new MalformedSuite(
                $methodMirror,
                'Setup methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isSuiteTeardown(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<teardown('suite')>>
        $teardown = $methodMirror->getAttribute('teardown');
        if(
            ! is_array($teardown) ||
            array_search('suite', $teardown) === false
        ) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(new MalformedSuite(
                $methodMirror,
                'Teardown methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isTestTeardown(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<teardown('test')>> or <<teardown>>
        $teardown = $methodMirror->getAttribute('teardown');
        if(
            ! is_array($teardown) ||
            (count($teardown) > 0 && array_search('test', $teardown) === false)
        ) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(new MalformedSuite(
                $methodMirror,
                'Teardown methods must not require parameters.',
            ));
            return false;
        }

        return true;
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
            $this->emitMalformedSuite(new MalformedSuite(
                $methodMirror,
                'Test methods must accept exactly 1 parameter of type HackPack\HackUnit\Assertion\AssertionBuilder',
            ));
            return false;
        }

        if($params->at(0)->getTypeText() !== AssertionBuilder::class) {
            $this->emitMalformedSuite(new MalformedSuite(
                $methodMirror,
                'Test methods must accept exactly 1 parameter of type HackPack\HackUnit\Assertion\AssertionBuilder',
            ));
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

    private function emitMalformedSuite(MalformedSuite $event) : void
    {
        foreach($this->malformedListeners as $l) {
            $l($event);
        }
    }

    public function onMalformedSuite((function(MalformedSuite):void) $listener) : this
    {
        $this->malformedListeners->add($listener);
        return $this;
    }
}
