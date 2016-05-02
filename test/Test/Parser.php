<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Parser;
use FredEmmott\DefinitionFinder\TreeParser;

class SuiteParserTest {
  private static string
    $suiteNamespace = 'HackPack\HackUnit\Tests\Fixtures\\';

  private static Map<string, Parser> $validParsersBySuiteName = Map {};
  private static Map<string, Parser> $invalidParsersBySuiteName = Map {};

  <<Setup('suite')>>
  public static function buildParsers(): void {
    self::$validParsersBySuiteName->addAll(
      TreeParser::FromPath(dirname(__DIR__).'/Fixtures/ValidSuites/')
        ->getClasses()
        ->map(
          $class ==> Pair {
            $class->getName(),
            new Parser($class->getName(), $class->getFileName()),
          },
        ),
    );

    self::$invalidParsersBySuiteName->addAll(
      TreeParser::FromPath(dirname(__DIR__).'/Fixtures/InvalidSuites/')
        ->getClasses()
        ->map(
          $class ==> Pair {
            $class->getName(),
            new Parser($class->getName(), $class->getFileName()),
          },
        ),
    );
  }

  private function parserFromSuiteName(string $name): Parser {
    $validFullName = self::$suiteNamespace.'ValidSuites\\'.$name;
    $invalidFullName = self::$suiteNamespace.'InvalidSuites\\'.$name;

    if (self::$validParsersBySuiteName->containsKey($validFullName)) {
      return self::$validParsersBySuiteName->at($validFullName);
    }

    if (self::$invalidParsersBySuiteName->containsKey($invalidFullName)) {
      return self::$invalidParsersBySuiteName->at($invalidFullName);
    }

    throw new \RuntimeException('Unable to locate suite '.$name);
  }

  <<Test>>
  public function validSuitesParseWithoutError(Assert $assert): void {
    foreach (self::$validParsersBySuiteName as $parser) {
      $assert->bool($parser->errors()->isEmpty())->is(true);
    }
  }

  <<Test>>
  public function factoryParsing(Assert $assert): void {
    $factoryList =
      $this->parserFromSuiteName('ConstructorIsDefaultWithNoParams')
        ->factories();

    $assert->int($factoryList->count())->eq(2);
    $assert->bool($factoryList->containsKey(''))->is(true);
    $assert->bool($factoryList->containsKey('named'))->is(true);
    $assert->string($factoryList->at(''))->is('__construct');
    $assert->string($factoryList->at('named'))->is('factory');

    $factoryList =
      $this->parserFromSuiteName('ConstructorIsDefaultWithParams')
        ->factories();

    $assert->int($factoryList->count())->eq(2);
    $assert->bool($factoryList->containsKey(''))->is(true);
    $assert->bool($factoryList->containsKey('named'))->is(true);
    $assert->string($factoryList->at(''))->is('__construct');
    $assert->string($factoryList->at('named'))->is('factory');

    $factoryList =
      $this->parserFromSuiteName('ConstructorIsNotDefault')
        ->factories();
    $assert->bool($factoryList->containsKey(''))->is(true);
    $assert->string($factoryList->at(''))->is('factory');

    $factoryList =
      $this->parserFromSuiteName('DerivedFactory')->factories();
    $assert->bool($factoryList->containsKey(''))->is(true);
    $assert->string($factoryList->at(''))->is('factory');

    $factoryList =
      $this->parserFromSuiteName('AbstractFactory')->factories();
    $assert->int($factoryList->count())->eq(0);
  }

  <<Test>>
  public function setupParsing(Assert $assert): void {
    $this->updownParsing('Setup', $assert);
  }

  <<Test>>
  public function teardownParsing(Assert $assert): void {
    $this->updownParsing('TearDown', $assert);
  }

  private function updownParsing(string $type, Assert $assert): void {
    $parser = $this->parserFromSuiteName($type);

    $expectedSuiteUp = Vector {'suiteOnly', 'both', 'nonRequiredParam'};

    $suiteUp = $type === 'Setup' ? $parser->suiteUp() : $parser->suiteDown();

    $extraSuiteUp = array_diff($suiteUp, $expectedSuiteUp);
    $missingSuiteUp = array_diff($expectedSuiteUp, $suiteUp);

    $assert->int(count($extraSuiteUp))->eq(0);
    $assert->int(count($missingSuiteUp))->eq(0);

    $expectedTestUp = Vector {'both', 'testOnlyExplicit', 'testOnlyImplicit'};
    $testUp = $type === 'Setup' ? $parser->testUp() : $parser->testDown();

    $extraTestUp = array_diff($testUp, $expectedTestUp);
    $missingTestUp = array_diff($expectedTestUp, $testUp);

    $assert->int(count($extraTestUp))->eq(0);
    $assert->int(count($missingTestUp))->eq(0);
  }

  <<Test>>
  public function testParsing(Assert $assert): void {
    $parser = $this->parserFromSuiteName('Test');

    $tests = Map::fromItems(
      $parser->tests()->map($test ==> Pair {$test['method'], $test}),
    );

    $testNames = $tests->keys();
    $expectedTestNames = Vector {
      'defaultProvider',
      'namedProvider',
      'staticTest',
      'skippedTest',
    };

    $missingTests = array_diff($expectedTestNames, $testNames);
    $extraTests = array_diff($testNames, $expectedTestNames);
    $assert->int(count($missingTests))->eq(0);
    $assert->int(count($extraTests))->eq(0);

    $defaultProvider = $tests->at('defaultProvider');
    $assert->string($defaultProvider['factory name'])->is('');
    $assert->bool($defaultProvider['skip'])->is(false);

    $defaultProvider = $tests->at('namedProvider');
    $assert->string($defaultProvider['factory name'])->is('named');
    $assert->bool($defaultProvider['skip'])->is(false);

    $defaultProvider = $tests->at('skippedTest');
    $assert->string($defaultProvider['factory name'])->is('');
    $assert->bool($defaultProvider['skip'])->is(true);
  }

  <<Test>>
  public function inheritedTests(Assert $assert): void {
    $parser = $this->parserFromSuiteName('BaseSuite');

    $tests = Map::fromItems(
      $parser->tests()->map($test ==> Pair {$test['method'], $test}),
    );

    $testNames = $tests->keys();
    $expectedTestNames = Vector {
      'testInsideBaseSuite',
      'testInsideAbstractSuite',
    };

    $missingTests = array_diff($expectedTestNames, $testNames);
    $extraTests = array_diff($testNames, $expectedTestNames);
    $assert->int(count($missingTests))->eq(0);
    $assert->int(count($extraTests))->eq(0);

    $parser = $this->parserFromSuiteName('HasExternalSuite');

    $tests = Map::fromItems(
      $parser->tests()->map($test ==> Pair {$test['method'], $test}),
    );

    $testNames = $tests->keys();
    $expectedTestNames = Vector {
      'testInsideBaseSuite',
      'testInsideAbstractSuite',
      'testInsideTrait',
    };

    $missingTests = array_diff($expectedTestNames, $testNames);
    $extraTests = array_diff($testNames, $expectedTestNames);
    $assert->int(count($missingTests))->eq(0);
    $assert->int(count($extraTests))->eq(0);
  }

  <<Test>>
  public function abstractClassesDoNotHaveTests(Assert $assert): void {
    $parser = $this->parserFromSuiteName('AbstractSuite');

    $assert->int($parser->tests()->count())->eq(0);
  }

  <<Test>>
  public function SuiteUpDownParseErrors(Assert $assert): void {
    $parser = $this->parserFromSuiteName('SuiteUpParams');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->suiteUp()->count())->eq(0);

    $parser = $this->parserFromSuiteName('SuiteUpNonStatic');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->suiteUp()->count())->eq(0);

    $parser = $this->parserFromSuiteName('SuiteDownParams');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->suiteDown()->count())->eq(0);

    $parser = $this->parserFromSuiteName('SuiteDownNonStatic');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->suiteDown()->count())->eq(0);
  }

  <<Test>>
  public function TestUpDownParseErrors(Assert $assert): void {
    $parser = $this->parserFromSuiteName('TestUpParams');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->testUp()->count())->eq(0);

    $parser = $this->parserFromSuiteName('TestUpConstructDestruct');
    $assert->int($parser->errors()->count())->eq(2);
    $assert->int($parser->testUp()->count())->eq(0);

    $parser =
      $this->parserFromSuiteName('TestDownConstructDestruct');
    $assert->int($parser->errors()->count())->eq(2);
    $assert->int($parser->testUp()->count())->eq(0);

    $parser = $this->parserFromSuiteName('TestUpParams');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->testUp()->count())->eq(0);

    $parser = $this->parserFromSuiteName('TestDownParams');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->testUp()->count())->eq(0);
  }

  <<Test>>
  public function TestParseErrors(Assert $assert): void {
    $parser = $this->parserFromSuiteName('TestConstructDestruct');
    $assert->int($parser->errors()->count())->eq(2);
    $assert->int($parser->tests()->count())->eq(0);

    $parser = $this->parserFromSuiteName('TestParams');
    $assert->int($parser->errors()->count())->eq(3);
    $assert->int($parser->tests()->count())->eq(0);
  }

  <<Test>>
  public function FactoryParseErrors(Assert $assert): void {
    $parser = $this->parserFromSuiteName('DuplicateFactories');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(1);

    $parser = $this->parserFromSuiteName('FactoryParams');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(0);

    $parser = $this->parserFromSuiteName('NonStaticFactory');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(0);

    $parser = $this->parserFromSuiteName('FactoryReturnType');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(0);

    $parser = $this->parserFromSuiteName('InvalidDerivedFactory');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(0);
  }
}
