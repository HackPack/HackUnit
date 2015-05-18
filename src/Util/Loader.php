<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Assertion\AssertionBuilder;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Suite;

final class Loader
{

    public function __construct(
        private Set<string> $includes = Set{},
        private Set<string> $excludes = Set{},
        private Vector<(function(MalformedSuite):void)> $malformedListeners = Vector{},
    )
    {
    }

    public function including(string $path) : this
    {
        $this->includes->add($path);
        return $this;
    }

    public function excluding(string $path) : this
    {
        $this->excludes->add($path);
        return $this;
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
        // Must have <<TestSuite>> attribute
        if($classMirror->getAttribute('TestSuite') === null) {
            return null;
        }

        $className = $classMirror->getName();
        $classFile = $classMirror->getFileName();
        if( ! is_string($className) || ! is_string($classFile)) {
            // Reflector unable to figure the class name/file
            // This indicates something is wrong, so conservatively do not
            // load this suite
            return null;
        }

        $constructor = $classMirror->getConstructor();
        if( $constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            // Test suites must never require params to be constructed.
            $this->emitMalformedSuite(MalformedSuite::badClass(
                $classMirror,
                'Test suite classes must not require parameters in their constructors.'
            ));
            return null;
        }

        $methods = (new Vector($classMirror->getMethods()))->filter($m ==> {
            $attrs = new Map($m->getAttributes());
            return
                $attrs->containsKey('Test') ||
                $attrs->containsKey('Setup') ||
                $attrs->containsKey('TearDown');
        });

        if($methods->isEmpty()) {
            return null;
        }

        $instance = $classMirror->newInstance();
        $suite = new Suite($classFile, $className);
        foreach($methods as $methodMirror) {
            if(
                $methodMirror->isAbstract() ||
                $methodMirror->isConstructor() ||
                $methodMirror->isDestructor() ||
                $methodMirror->isStatic()
            ) {
                // Must be normal instance method
                $this->emitMalformedSuite(MalformedSuite::badMethod(
                    $methodMirror,
                    'Setup, TearDown, and Test methods must be instance methods and must not be the constructor, the destructor, nor be abstract.',
                ));
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
                $suite->registerTest(
                    (AssertionBuilder $builder) ==> {
                        $methodMirror->invoke($instance, $builder);
                    },
                    $methodMirror,
                );
            }
        }

        return $suite;
    }

    private function isSuiteSetup(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<Setup('suite')>>
        $setup = $methodMirror->getAttribute('Setup');
        if(! is_array($setup) || array_search('suite', $setup) === false) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(MalformedSuite::badMethod(
                $methodMirror,
                'Setup methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isTestSetup(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<Setup('test')>> or <<Setup>>
        $setup = $methodMirror->getAttribute('Setup');
        if(
            ! is_array($setup) ||
            (count($setup) > 0 && array_search('suite', $setup) === false)
        ) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(MalformedSuite::badMethod(
                $methodMirror,
                'Setup methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isSuiteTeardown(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<TearDown('suite')>>
        $teardown = $methodMirror->getAttribute('TearDown');
        if(
            ! is_array($teardown) ||
            array_search('suite', $teardown) === false
        ) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(MalformedSuite::badMethod(
                $methodMirror,
                'Tear down methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isTestTeardown(\ReflectionMethod $methodMirror) : bool
    {
        // Need to mark with <<TearDown('test')>> or <<TearDown>>
        $teardown = $methodMirror->getAttribute('TearDown');
        if(
            ! is_array($teardown) ||
            (count($teardown) > 0 && array_search('test', $teardown) === false)
        ) {
            return false;
        }

        // No parameters
        if($methodMirror->getNumberOfRequiredParameters() !== 0) {
            $this->emitMalformedSuite(MalformedSuite::badMethod(
                $methodMirror,
                'Teardown methods must not require parameters.',
            ));
            return false;
        }

        return true;
    }

    private function isTest(\ReflectionMethod $methodMirror) : bool
    {
        // Look for <<Test>> attribute
        if($methodMirror->getAttribute('Test') === null) {
            return false;
        }

        // Ensure method takes an AssertionBuilder as the only parameter
        $params = new Vector($methodMirror->getParameters());

        if($params->count() !== 1) {
            $this->emitMalformedSuite(MalformedSuite::badMethod(
                $methodMirror,
                'Test methods must accept exactly 1 parameter of type HackPack\HackUnit\Assertion\AssertionBuilder',
            ));
            return false;
        }

        if($params->at(0)->getTypeText() !== AssertionBuilder::class) {
            $this->emitMalformedSuite(MalformedSuite::badMethod(
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
