<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\Trace;
use ReflectionClass;
use ReflectionMethod;

class Parser implements \HackPack\HackUnit\Contract\Test\Parser {
  private Vector<MalformedSuite> $errors = Vector {};
  private Map<string, string> $factories = Map {};
  private Map<string,
  shape(
    'method' => string,
    'data type' => string,
  )> $dataProviders = Map {};
  private Vector<string> $suiteUp = Vector {};
  private Vector<string> $suiteDown = Vector {};
  private Vector<string> $testUp = Vector {};
  private Vector<string> $testDown = Vector {};
  private Vector<shape(
    'factory name' => string,
    'method' => string,
    'skip' => bool,
    'data provider' => string,
  )> $tests = Vector {};

  private ReflectionClass $class;

  public function __construct(string $className, string $fileName) {
    $this->ensureClassExists($className, $fileName);

    $this->class = new ReflectionClass($className);

    // Check the constructor last to allow declared default factory
    $constructor = null;

    // Only look for tests if the class is instantiable
    if ($this->class->isAbstract()) {
      return;
    }

    // Need to identify data providers before validating test methods
    $this->extractDataProviders();

    foreach ($this->class->getMethods() as $method) {
      if ($method->isConstructor()) {
        $constructor = $method;
        continue;
      }
      $this->categorize($method);
    }

    if ($constructor !== null) {
      $this->categorize($constructor);
    }
  }

  public function factories(): \ConstMap<string, string> {
    return $this->factories;
  }

  public function suiteUp(): \ConstVector<string> {
    return $this->suiteUp;
  }

  public function suiteDown(): \ConstVector<string> {
    return $this->suiteDown;
  }

  public function testUp(): \ConstVector<string> {
    return $this->testUp;
  }

  public function testDown(): \ConstVector<string> {
    return $this->testDown;
  }

  public function tests(
  ): \ConstVector<shape(
    'factory name' => string,
    'method' => string,
    'skip' => bool,
    'data provider' => string,
  )> {
    return $this->tests;
  }

  public function errors(): \ConstVector<MalformedSuite> {
    return $this->errors;
  }

  private function categorize(ReflectionMethod $method): void {
    if ($method->isConstructor()) {
      $this->checkConstructor($method);
    }

    $attrs = new Map($method->getAttributes());
    if (!$attrs->containsKey('Test') &&
        !$attrs->containsKey('Setup') &&
        !$attrs->containsKey('TearDown') &&
        !$attrs->containsKey('SuiteProvider')) {
      // This method can't be categorized
      return;
    }

    if ($method->isConstructor() || $method->isDestructor()) {
      // Must be normal instance method
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Setup, TearDown, and Test methods must be instance methods and must not be the constructor, the destructor, nor be abstract.',
          ),
        );
      return;
    }

    $this->checkUpDown('Setup', $method);
    $this->checkUpDown('TearDown', $method);
    $this->checkTest($method);
    $this->checkFactory($method);
  }

  private function checkUpDown(string $type, ReflectionMethod $method): void {
    $attr = (new Map($method->getAttributes()))->get($type);
    if ($attr === null) {
      return;
    }

    // No parameters
    if ($method->getNumberOfRequiredParameters() > 0) {
      $this->errors->add(
        new MalformedSuite(
          Trace::fromReflectionMethod($method),
          $type.' methods must not require parameters.',
        ),
      );
      return;
    }

    $attr = (new Set($attr))->map($v ==> strtolower($v));

    // Look for <<$type('suite')>>
    if ($attr->contains('suite')) {
      // Suite setup/teardown must be static
      if (!$method->isStatic()) {
        $this->errors->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Suite '.$type.' methods must be declared static.',
          ),
        );
        return;
      }

      if ($type === 'Setup') {
        $this->suiteUp->add($method->getName());
      }
      if ($type === 'TearDown') {
        $this->suiteDown->add($method->getName());
      }
    }

    // Look for <<$type('test')>> and <<$type>>
    if ($attr->isEmpty() || $attr->contains('test')) {
      if ($type === 'Setup') {
        $this->testUp->add($method->getName());
      }
      if ($type === 'TearDown') {
        $this->testDown->add($method->getName());
      }
    }
  }

  private function checkTest(ReflectionMethod $method): void {
    // Look for <<Test>> attribute
    $testAttr = (new Map($method->getAttributes()))->get('Test');
    if ($testAttr === null) {
      return;
    }

    // If no value, this will be null cast to a string
    // and the empty string is the alias for the default factory
    $factory = (string) (new Vector($testAttr))->get(0);

    $dataAttr = $method->getAttribute('Data');
    if ($dataAttr === null) {
      $dataProvider = null;
    } else {
      $dataProvider = $this->determineDataProviderForTest($method, $dataAttr);
      if ($dataProvider === null) {
        return;
      }
    }

    if ($this->testParamsAreInvalid($method, $dataProvider)) {
      return;
    }

    $this->tests->add(
      shape(
        'factory name' => $factory,
        'method' => $method->getName(),
        'skip' => (new Map($method->getAttributes()))->containsKey('Skip'),
        'data provider' =>
          $dataProvider === null ? '' : $dataProvider['method'],
      ),
    );
  }

  private function determineDataProviderForTest(
    ReflectionMethod $method,
    array<mixed> $dataAttr,
  ): ?shape(
    'method' => string,
    'data type' => string,
  ) {
    if (count($dataAttr) < 1) {
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'You must specify the data provider to use, e.g. <<Test, Data(\'my provider\')>>',
          ),
        );
      return null;
    }

    $provider = $this->dataProviders->get((string) $dataAttr[0]);
    if ($provider === null) {
      $this->errors->add(
        new MalformedSuite(
          Trace::fromReflectionMethod($method),
          'Unknown data provider "'.(string) $dataAttr[0].'"',
        ),
      );
      return null;
    }

    return $provider;
  }

  private function testParamsAreInvalid(
    ReflectionMethod $method,
    ?shape(
      'method' => string,
      'data type' => string,
    ) $dataProvider,
  ): bool {

    $expectedParams = Vector {Assert::class};
    if ($dataProvider !== null) {
      $expectedParams->add($dataProvider['data type']);
    }

    $params = new Vector($method->getParameters());
    $invalidParams = $params->filterWithKey(
      ($index, $parameter) ==> {
        if (!$expectedParams->containsKey($index)) {
          return true;
        }

        return $parameter->getTypeText() !== $expectedParams->at($index);
      },
    );

    if ($invalidParams->count() > 0 || $params->isEmpty()) {
      sprintf(
        'Test method %s must accept exactly %d %s %s',
        $method->getName(),
        $expectedParams->count(),
        $expectedParams->count() === 1 ? 'type' : 'types',
        implode(' and ', $expectedParams),
      )
        |> $this->errors->add(
          new MalformedSuite(Trace::fromReflectionMethod($method), $$),
        );
      return true;
    }
    return false;
  }

  private function checkFactory(ReflectionMethod $method): void {
    $attr = (new Map($method->getAttributes()))->get('SuiteProvider');
    if ($attr === null) {
      return;
    }

    $attr = new Vector($attr);

    $alias = $attr->count() > 0 ? $attr->at(0) : '';
    if (!is_string($alias)) {
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'The <<SuiteProvider(\'name\')>> annotation must have exactly one string parameter',
          ),
        );
      return;
    }

    if ($this->factories->containsKey($alias)) {
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'The <<SuiteProvider(\'name\')>> annotation must have a unique name',
          ),
        );
      return;
    }

    if (!$method->isStatic()) {
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Suite factories annotated with <<SuiteProvider>> must be declared static',
          ),
        );
      return;
    }

    if ($method->getNumberOfRequiredParameters() > 0) {
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Suite factories annotated with <<SuiteProvider>> not require any input parameters',
          ),
        );
      return;
    }

    $returnType = (string) $method->getReturnType();
    if (!$this->producesSuiteInstance($method)) {
      $this->errors
        ->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Suite factories annotated with <<SuiteProvider>> must return an instance of the suite class',
          ),
        );
      return;
    }

    $this->factories->set($alias, $method->getName());
  }

  private function producesSuiteInstance(ReflectionMethod $method): bool {
    $returnType = $method->getReturnType();

    if ($returnType === null ||
        $returnType->isBuiltin() ||
        $returnType->allowsNull()) {
      return false;
    }

    $returnString = (string) $returnType;

    return
      $returnString === 'HH\this' ||
      $this->class->getName() === $returnString;

  }

  private function extractDataProviders(): void {
    foreach ($this->class->getMethods() as $method) {

      $dataAttribute = $method->getAttribute('DataProvider');
      if ($dataAttribute === null) {
        continue;
      }
      if (count($dataAttribute) < 1) {
        $this->errors
          ->add(
            new MalformedSuite(
              Trace::fromReflectionMethod($method),
              'Data providers must have a name, e.g. <<DataProvider(\'my provider\')>>',
            ),
          );
        continue;
      }

      $providerName = $dataAttribute[0];
      if (!is_string($providerName)) {
        $this->errors->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Data provider names must be strings.',
          ),
        );
        continue;
      }

      if (!$method->isStatic()) {
        $this->errors->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Data providers must be static methods.',
          ),
        );
        continue;
      }

      if ($method->getNumberOfRequiredParameters() !== 0) {
        $this->errors->add(
          new MalformedSuite(
            Trace::fromReflectionMethod($method),
            'Data providers must not require input parameters.',
          ),
        );
        continue;
      }

      $dataType = $this->determineDataProviderType($method);
      if ($dataType === null) {
        continue;
      }

      $this->dataProviders->set(
        $providerName,
        shape('method' => $method->getName(), 'data type' => $dataType),
      );
    }
  }

  private function determineDataProviderType(
    ReflectionMethod $method,
  ): ?string {

    // Ensure we have a return type
    $return = $method->getReturnType();
    if ($return === null) {
      $this->errors->add(
        new MalformedSuite(
          Trace::fromReflectionMethod($method),
          'You must specify a return type for data provider methods.',
        ),
      );
      return null;
    }

    // No nullable types
    if ($return->allowsNull()) {
      $this->errors->add(
        new MalformedSuite(
          Trace::fromReflectionMethod($method),
          'Data provider methods may not return nullable types.',
        ),
      );
      return null;
    }

    // Ensure return is foreachable
    $typeString = (string) $return;
    $expectedStart =
      $method->isAsync() ? 'HH\AsyncIterator<' : 'HH\Traversable<';
    if (substr($typeString, 0, strlen($expectedStart)) !== $expectedStart) {
      $message =
        $method->isAsync()
          ? 'Async data providers must return AsyncIterator<type>'
          : 'Data providers must return a Traversable<type>';
      $this->errors->add(
        new MalformedSuite(Trace::fromReflectionMethod($method), $message),
      );
      return null;
    }

    // The rest of the return string is the type being passed to the test
    return substr($typeString, strlen($expectedStart), -1);
  }

  private function checkConstructor(ReflectionMethod $method): void {
    if ($method === null) {
      // No constructor, oh well. :)
      return;
    }

    // Don't overwrite the default factory
    if ($this->factories->containsKey('')) {
      return;
    }

    if ($method->getNumberOfRequiredParameters() === 0) {
      $this->factories->set('', $method->getName());
    }
  }

  private function ensureClassExists(
    string $className,
    string $fileName,
  ): void {
    if (!class_exists($className)) {

      if (is_file($fileName)) {
        /* HH_FIXME[1002] */
        require_once ($fileName);
      }

      if (!class_exists($className)) {
        throw new \RuntimeException('Unable to locate class '.$className);
      }

    }
  }
}
