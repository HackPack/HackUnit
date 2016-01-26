<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\Trace;
use ReflectionClass;
use ReflectionMethod;

class SuiteParser implements \HackPack\HackUnit\Contract\Test\SuiteParser
{
    private Vector<MalformedSuite> $errors = Vector{};
    private Map<string, (function():Awaitable<mixed>)> $factories = Map{};
    private Vector<ReflectionMethod> $suiteUp = Vector{};
    private Vector<ReflectionMethod> $suiteDown = Vector{};
    private Vector<ReflectionMethod> $testUp = Vector{};
    private Vector<ReflectionMethod> $testDown = Vector{};
    private Vector<
        shape(
            'factory name' => string,
            'method' => ReflectionMethod,
            'skip' => bool,
        )
    > $tests = Vector{};

    public function __construct(private ReflectionClass $classMirror)
    {
        $this->checkConstructor();

        foreach($classMirror->getMethods() as $method) {
            $this->categorize($method);
        }
    }

    public function factories() : \ConstMap<string, (function():mixed)>
    {
        return $this->factories;
    }

    public function suiteUp() : \ConstVector<ReflectionMethod>
    {
        return $this->suiteUp;
    }

    public function suiteDown() : \ConstVector<ReflectionMethod>
    {
        return $this->suiteDown;
    }

    public function testUp() : \ConstVector<ReflectionMethod>
    {
        return $this->testUp;
    }

    public function testDown() : \ConstVector<ReflectionMethod>
    {
        return $this->testDown;
    }

    public function tests() : \ConstVector<
        shape(
            'factory name' => string,
            'method' => ReflectionMethod,
            'skip' => bool,
        )
    >
    {
        return $this->tests;
    }

    public function errors() : \ConstVector<MalformedSuite>
    {
         return $this->errors;
    }

    private function checkConstructor() : void
    {
        $constructor = $this->classMirror->getConstructor();
        if(
            ($constructor instanceof ReflectionMethod) &&
            $constructor->getNumberOfRequiredParameters() === 0
        ) {
            $this->factories->set('', () ==> $this->classMirror->newInstance());
            return;
        }

        if($constructor === null) {
            $this->factories->set('', () ==> $this->classMirror->newInstanceWithoutConstructor());
        }
    }

    private function categorize(ReflectionMethod $method) : void
    {
        $attrs = new Map($method->getAttributes());
        if(
            !$attrs->containsKey('Test') &&
            !$attrs->containsKey('Setup') &&
            !$attrs->containsKey('TearDown') &&
            !$attrs->containsKey('Provides')
        ) {
            // This method can't be categorized
            return;
        }

        if(
            $method->isAbstract() ||
            $method->isConstructor() ||
            $method->isDestructor()
        ) {
            // Must be normal instance method
            $this->errors->add(new MalformedSuite(
                Trace::fromReflectionMethod($method),
                'Setup, TearDown, and Test methods must be instance methods and must not be the constructor, the destructor, nor be abstract.',
            ));
            return;
        }

        $this->checkUpDown('Setup', $method);
        $this->checkUpDown('TearDown', $method);
        $this->checkTest($method);
        $this->checkFactory($method);
    }

    private function checkUpDown(string $type, ReflectionMethod $method) : void
    {
        $attr = $method->getAttribute($type);
        if(! is_array($attr)) {
            return;
        }

        // No parameters
        if($method->getNumberOfRequiredParameters() !== 0) {
            $this->errors->add(new MalformedSuite(
                Trace::fromReflectionMethod($method),
                $type . ' methods must not require parameters.',
            ));
            return;
        }

        $attr = (new Set($attr))->map($v ==> strtolower($v));

        // Look for <<$type('suite')>>
        if($attr->contains('suite')) {
            // Suite setup/teardown must be static
            if(!$method->isStatic()) {
                $this->errors->add(new MalformedSuite(
                    Trace::fromReflectionMethod($method),
                    'Suite ' . $type . ' methods must be declared static.',
                ));
                return;
            }

            if($type === 'Setup') {
                $this->suiteUp->add($method);
                return;
            }
            if($type === 'TearDown') {
                $this->suiteDown->add($method);
            }
        }

        if($attr->isEmpty() || $attr->contains('test')) {
            if($type === 'Setup') {
                $this->testUp->add($method);
                return;
            }
            if($type === 'TearDown') {
                $this->testDown->add($method);
            }
        }
    }

    private function checkTest(ReflectionMethod $method) : void
    {
        // Look for <<Test>> attribute
        $attr = $method->getAttribute('Test');
        if(!is_array($attr)) {
            return;
        }

        // Ensure method takes an Assert as the only parameter
        $params = new Vector($method->getParameters());

        if(
            $params->count() !== 1 ||
            $params->at(0)->getTypeText() !== Assert::class
        ) {
            $this->errors->add(new MalformedSuite(
                Trace::fromReflectionMethod($method),
                'Test methods must accept exactly 1 parameter of type HackPack\HackUnit\Contract\Assert',
            ));
            return;
        }

        $factory = '';
        if(count($attr) > 0 && is_string($attr[0])) {
            $factory = $attr[0];
        }

        $this->tests->add(
            shape(
                'factory name' => $factory,
                'method' => $method,
                'skip' => is_array($method->getAttribute('Skip')),
            )
        );
    }

    private function checkFactory(ReflectionMethod $method) : void
    {
        $attr = $method->getAttribute('SuiteProvider');
        if(!is_array($attr)) {
            return;
        }

        if(count($attr) != 1 || ! is_string($attr[0])) {
            $this->errors->add(new MalformedSuite(
                Trace::fromReflectionMethod($method),
                'The <<SuiteProvider(\'name\')>> annotation must have exactly one string parameter',
            ));
            return;
        }

        if(! $method->isStatic()) {
            $this->errors->add(new MalformedSuite(
                Trace::fromReflectionMethod($method),
                'Suite factories annotated with <<SuiteProvider>> must be declared static',
            ));
            return;
        }

        $name = count($attr) === 0 ? '' : (string)$attr[0];

        $this->factories->set(
            $name,
            async () ==> {
                $result = $method->invoke(null);
                if($method->isAsync()) {
                    $result = await $result;
                }
                return $result;
            }
        );
    }
}
