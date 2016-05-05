<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Parser;
use FredEmmott\DefinitionFinder\TreeParser;

class ValidParsing extends ParserTest {

  protected static function basePath(): string {
    return dirname(__DIR__).'/Fixtures/ValidSuites';
  }

  protected static function fullName(string $name): string {
    return 'HackPack\\HackUnit\\Tests\\Fixtures\\ValidSuites\\'.$name;
  }

  <<Test>>
  public function validSuitesParseWithoutError(Assert $assert): void {
    foreach (self::$parsersBySuiteName as $parser) {
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
      $this->parserFromSuiteName('ConstructorIsNotDefault')->factories();
    $assert->bool($factoryList->containsKey(''))->is(true);
    $assert->string($factoryList->at(''))->is('factory');

    $factoryList = $this->parserFromSuiteName('DerivedFactory')->factories();
    $assert->bool($factoryList->containsKey(''))->is(true);
    $assert->string($factoryList->at(''))->is('factory');

    $factoryList = $this->parserFromSuiteName('AbstractFactory')->factories();
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
    $suiteUp = $type === 'Setup' ? $parser->suiteUp() : $parser->suiteDown();
    $assert->container($suiteUp)
      ->containsOnly(['suiteOnly', 'both', 'nonRequiredParam']);

    $expectedTestUp = Vector {};
    $testUp = $type === 'Setup' ? $parser->testUp() : $parser->testDown();
    $assert->container($testUp)
      ->containsOnly(['both', 'testOnlyExplicit', 'testOnlyImplicit']);
  }

  <<Test>>
  public function testParsing(Assert $assert): void {
    $parser = $this->parserFromSuiteName('Test');

    $tests = Map::fromItems(
      $parser->tests()->map($test ==> Pair {$test['method'], $test}),
    );

    $expectedTestNames = Vector {
      'defaultSuiteProvider',
      'namedSuiteProvider',
      'staticTest',
      'skippedTest',
    };

    $assert->container($tests->keys())->containsOnly($expectedTestNames);

    $defaultProvider = $tests->at('defaultSuiteProvider');
    $assert->string($defaultProvider['factory name'])->is('');
    $assert->bool($defaultProvider['skip'])->is(false);

    $defaultProvider = $tests->at('namedSuiteProvider');
    $assert->string($defaultProvider['factory name'])->is('named');
    $assert->bool($defaultProvider['skip'])->is(false);

    $defaultProvider = $tests->at('skippedTest');
    $assert->string($defaultProvider['factory name'])->is('');
    $assert->bool($defaultProvider['skip'])->is(true);
  }

  <<Test>>
  public function inheritedTests(Assert $assert): void {
    $parser = $this->parserFromSuiteName('BaseSuite');
    $testNames = $parser->tests()->map($test ==> $test['method']);
    $assert->container($testNames)
      ->containsOnly(['testInsideBaseSuite', 'testInsideAbstractSuite']);

    $parser = $this->parserFromSuiteName('HasExternalSuite');
    $testNames = $parser->tests()->map($test ==> $test['method']);
    $assert->container($testNames)->containsOnly(
      ['testInsideBaseSuite', 'testInsideAbstractSuite', 'testInsideTrait'],
    );
  }

  <<Test>>
  public function abstractClassesDoNotHaveTests(Assert $assert): void {
    $parser = $this->parserFromSuiteName('AbstractSuite');

    $assert->int($parser->tests()->count())->eq(0);
  }

  <<Test>>
  public function validDataProvider(Assert $assert): void {
    $tests = Map::fromItems(
      $this->parserFromSuiteName('Data')
        ->tests()
        ->map($test ==> Pair {$test['method'], $test}),
    );
    $assert->container($tests->keys())
      ->containsOnly(['consumesVector', 'consumesMap', 'consumesString']);

    $assert->string($tests->at('consumesVector')['data provider'])
      ->is('vectorProvider');
    $assert->string($tests->at('consumesMap')['data provider'])
      ->is('mapProvider');
    $assert->string($tests->at('consumesString')['data provider'])
      ->is('stringProvider');
  }

  <<Test>>
  public function asyncDataProvider(Assert $assert): void {
    $tests = Map::fromItems(
      $this->parserFromSuiteName('AsyncData')
        ->tests()
        ->map($test ==> Pair {$test['method'], $test}),
    );
    $assert->container($tests->keys())->containsOnly(['asyncConsumer']);

    $assert->string($tests->at('asyncConsumer')['data provider'])
      ->is('asyncDataProvider');
  }
}
