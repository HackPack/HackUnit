<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\Trace;
use ReflectionClass;
use ReflectionMethod;

class Parser implements \HackPack\HackUnit\Contract\Test\Parser
{
    private Vector<MalformedSuite> $errors = Vector{};
    private Map<string, string> $factories = Map{};
    private Vector<string> $suiteUp = Vector{};
    private Vector<string> $suiteDown = Vector{};
    private Vector<string> $testUp = Vector{};
    private Vector<string> $testDown = Vector{};
    private Vector<
        shape(
            'factory name' => string,
            'method' => string,
            'skip' => bool,
        )
    > $tests = Vector{};

    private ReflectionClass $class;

    public function __construct(string $className, string $fileName)
    {
        $this->ensureClassExists($className, $fileName);

        $this->class = new ReflectionClass($className);

        // Only look for tests if the class is instantiable
        if( ! $this->class->isAbstract()) {
            foreach($this->class->getMethods() as $method) {
                $this->categorize($method);
            }
        }
    }

    public function factories() : \ConstMap<string, string>
    {
        return $this->factories;
    }

    public function suiteUp() : \ConstVector<string>
    {
        return $this->suiteUp;
    }

    public function suiteDown() : \ConstVector<string>
    {
        return $this->suiteDown;
    }

    public function testUp() : \ConstVector<string>
    {
        return $this->testUp;
    }

    public function testDown() : \ConstVector<string>
    {
        return $this->testDown;
    }

    public function tests() : \ConstVector<
        shape(
            'factory name' => string,
            'method' => string,
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

    private function categorize(ReflectionMethod $method) : void
    {
        if($method->getName() === '__construct') {
            $this->checkConstructor($method);
            return;
        }

        $attrs = new Map($method->getAttributes());
        if(
            !$attrs->containsKey('Test') &&
            !$attrs->containsKey('Setup') &&
            !$attrs->containsKey('TearDown') &&
            !$attrs->containsKey('SuiteProvider')
        ) {
            // This method can't be categorized
            return;
        }

        if($method->getName() === '__destruct') {
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
        $attr = (new Map($method->getAttributes()))->get($type);
        if($attr === null) {
            return;
        }

        // No parameters
        $requiredParams = (new Vector($method->getParameters()))->filter($p ==> !$p->isOptional());
        if($requiredParams->count() !== 0) {
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
                $this->suiteUp->add($method->getName());
            }
            if($type === 'TearDown') {
                $this->suiteDown->add($method->getName());
            }
        }

        // Look for <<$type('test')>> and <<$type>>
        if($attr->isEmpty() || $attr->contains('test')) {
            if($type === 'Setup') {
                $this->testUp->add($method->getName());
            }
            if($type === 'TearDown') {
                $this->testDown->add($method->getName());
            }
        }
    }

    private function checkTest(ReflectionMethod $method) : void
    {
        // Look for <<Test>> attribute
        $attr = (new Map($method->getAttributes()))->get('Test');
        if($attr === null) {
            return;
        }

        // If no value, this will be null cast to a string
        // and the empty string is the alias for the default factory
        $factory = (string)(new Vector($attr))->get(0);

        // Ensure method takes an Assert as the only parameter
        $params = new Vector($method->getParameters());

        if(
            $params->count() !== 1 ||
            $params->at(0)->getType()?->__toString() !== Assert::class
        ) {
            $this->errors->add(new MalformedSuite(
                Trace::fromReflectionMethod($method),
                'Test methods must accept exactly 1 parameter of type HackPack\HackUnit\Contract\Assert',
            ));
            return;
        }

        $this->tests->add(
            shape(
                'factory name' => $factory,
                'method' => $method->getName(),
                'skip' => (new Map($method->getAttributes()))->containsKey('Skip'),
            )
        );
    }

    private function checkFactory(ReflectionMethod $method) : void
    {
        $attr = (new Map($method->getAttributes()))->get('SuiteProvider');
        if($attr === null) {
            return;
        }

        $attr = new Vector($attr);

        $alias = $attr->count() > 0 ? $attr->at(0) : '';
        if(!is_string($alias)) {
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

        $this->factories->set($alias, $method->getName());
    }

    private function checkConstructor(ReflectionMethod $method) : void
    {
        // Don't overwrite the default factory
        if($this->factories->containsKey('')) {
            return;
        }

        if($method->getNumberOfRequiredParameters() === 0) {
            $this->factories->set('', $method->getName());
        }
    }

    private function ensureClassExists(string $className, string $fileName) : void
    {
        if( ! class_exists($className)) {

            if(is_file($fileName)) {
                /* HH_FIXME[1002] */
                require_once($fileName);
            }

            if( ! class_exists($className) ) {
                 throw new \RuntimeException('Unable to locate class ' . $className);
            }

        }
    }
}
