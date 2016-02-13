<?hh // strict

namespace HackPack\HackUnit\Test;

use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedMethod;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\Trace;

class SuiteParser implements \HackPack\HackUnit\Contract\Test\SuiteParser
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

    public function __construct(private ScannedBasicClass $class)
    {
        foreach($class->getMethods() as $method) {
            $this->categorize($method);
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

    private function categorize(ScannedMethod $method) : void
    {
        if($method->getName() === '__construct') {
            $this->checkConstructor($method);
            return;
        }

        $attrs = $method->getAttributes();
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
                Trace::fromScannedMethod($this->class, $method),
                'Setup, TearDown, and Test methods must be instance methods and must not be the constructor, the destructor, nor be abstract.',
            ));
            return;
        }

        $this->checkUpDown('Setup', $method);
        $this->checkUpDown('TearDown', $method);
        $this->checkTest($method);
        $this->checkFactory($method);
    }

    private function checkUpDown(string $type, ScannedMethod $method) : void
    {
        $attr = $method->getAttributes()->get($type);
        if($attr === null) {
            return;
        }

        // No parameters
        $requiredParams = $method->getParameters()->filter($p ==> !$p->isOptional());
        if($requiredParams->count() !== 0) {
            $this->errors->add(new MalformedSuite(
                Trace::fromScannedMethod($this->class, $method),
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
                    Trace::fromScannedMethod($this->class, $method),
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

    private function checkTest(ScannedMethod $method) : void
    {
        // Look for <<Test>> attribute
        $attr = $method->getAttributes()->get('Test');
        if($attr === null) {
            return;
        }

        // If no value, this will be null cast to a string
        // and the empty string is the alias for the default factory
        $factory = (string)$attr->get(0);

        // Ensure method takes an Assert as the only parameter
        $params = $method->getParameters();

        if(
            $params->count() !== 1 ||
            $params->at(0)->getTypehint()?->getTypeName() !== Assert::class
        ) {
            $this->errors->add(new MalformedSuite(
                Trace::fromScannedMethod($this->class, $method),
                'Test methods must accept exactly 1 parameter of type HackPack\HackUnit\Contract\Assert',
            ));
            return;
        }

        $this->tests->add(
            shape(
                'factory name' => $factory,
                'method' => $method->getName(),
                'skip' => $method->getAttributes()->containsKey('Skip'),
            )
        );
    }

    private function checkFactory(ScannedMethod $method) : void
    {
        $attr = $method->getAttributes()->get('SuiteProvider');
        if($attr === null) {
            return;
        }

        $alias = $attr->count() > 0 ? $attr->at(0) : '';
        if(!is_string($alias)) {
            $this->errors->add(new MalformedSuite(
                Trace::fromScannedMethod($this->class, $method),
                'The <<SuiteProvider(\'name\')>> annotation must have exactly one string parameter',
            ));
            return;
        }

        if(! $method->isStatic()) {
            $this->errors->add(new MalformedSuite(
                Trace::fromScannedMethod($this->class, $method),
                'Suite factories annotated with <<SuiteProvider>> must be declared static',
            ));
            return;
        }

        $this->factories->set($alias, $method->getName());
    }

    private function checkConstructor(ScannedMethod $method) : void
    {
        // Don't overwrite the default factory
        if($this->factories->containsKey('')) {
            return;
        }

        $requiredParams = $method->getParameters()->filter($p ==> !$p->isOptional());
        if($requiredParams->isEmpty()) {
            $this->factories->set('', $method->getName());
        }
    }
}
