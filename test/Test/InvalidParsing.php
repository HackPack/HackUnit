<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Parser;
use FredEmmott\DefinitionFinder\TreeParser;

type ErrorSuite = shape(
  'name' => string,
  'errors' => int,
);

class InvalidParsing extends ParserTest {

  protected static function basePath(): string {
    return dirname(__DIR__).'/Fixtures/InvalidSuites';
  }

  protected static function fullName(string $name): string {
    return 'HackPack\\HackUnit\\Tests\\Fixtures\\InvalidSuites\\'.$name;
  }

  <<Test>>
  public function factoryParsing(Assert $assert): void {
    $factoryList = $this->parserFromSuiteName('AbstractFactory')->factories();
    $assert->int($factoryList->count())->eq(0);
  }

  <<DataProvider('invalid suite up down')>>
  public static function invalidSuiteUpDown(): Traversable<string> {
    return [
      'SuiteUpParams',
      'SuiteUpNonStatic',
      'SuiteDownParams',
      'SuiteDownNonStatic',
    ];
  }

  <<Test, Data('invalid suite up down')>>
  public function SuiteUpDownParseErrors(
    Assert $assert,
    string $suiteName,
  ): void {
    $parser = $this->parserFromSuiteName($suiteName);
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->suiteUp()->count())->eq(0);
  }

  <<DataProvider('invalid test up down')>>
  public static function invalidUpDownSuites(): Traversable<ErrorSuite> {
    return [
      shape('errors' => 1, 'name' => 'TestUpParams'),
      shape('errors' => 2, 'name' => 'TestUpConstructDestruct'),
      shape('errors' => 2, 'name' => 'TestDownConstructDestruct'),
      shape('errors' => 1, 'name' => 'TestUpParams'),
      shape('errors' => 1, 'name' => 'TestDownParams'),
    ];
  }
  <<Test, Data('invalid test up down')>>
  public function TestUpDownParseErrors(
    Assert $assert,
    ErrorSuite $suiteData,
  ): void {
    $parser = $this->parserFromSuiteName($suiteData['name']);
    $assert->int($parser->errors()->count())->eq($suiteData['errors']);
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

  <<DataProvider('invalid factories')>>
  public static function invalidFactories(): Traversable<string> {
    return [
      'FactoryParams',
      'NonStaticFactory',
      'FactoryReturnType',
      'InvalidDerivedFactory',
    ];
  }

  <<Test, Data('invalid factories')>>
  public function FactoryParseErrors(Assert $assert, string $suiteName): void {
    $parser = $this->parserFromSuiteName($suiteName);
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(0);
  }

  <<Test>>
  public function duplicateFactories(Assert $assert): void {
    $parser = $this->parserFromSuiteName('DuplicateFactories');
    $assert->int($parser->errors()->count())->eq(1);
    $assert->int($parser->factories()->count())->eq(1);
  }

  <<DataProvider('invalid data providers')>>
  public static function invalidDataProviders(): Traversable<ErrorSuite> {
    return [
      shape('errors' => 1, 'name' => 'DataProviderMissingName'),
      shape('errors' => 1, 'name' => 'DataProviderWithParams'),
      shape('errors' => 1, 'name' => 'InstanceDataProvider'),
      shape('errors' => 3, 'name' => 'NonTraversableDataProvider'),
    ];
  }

  <<Test, Data('invalid data providers')>>
  public function invalidDataProvider(
    Assert $assert,
    ErrorSuite $suiteData,
  ): void {
    $parser = $this->parserFromSuiteName($suiteData['name']);
    $assert->int($parser->errors()->count())->eq($suiteData['errors']);
    $assert->int($parser->tests()->count())->eq(0);
  }

  <<DataProvider('invalid data consumers')>>
  public static function invalidDataConsumers(): Traversable<ErrorSuite> {
    return [
      shape('errors' => 1, 'name' => 'DataConsumerMismatchParams'),
      shape('errors' => 1, 'name' => 'DataConsumerMissingName'),
      shape('errors' => 2, 'name' => 'DataConsumerMismatchGeneric'),
    ];
  }

  <<Test, Data('invalid data consumers')>>
  public function invalidDataConsumer(
    Assert $assert,
    ErrorSuite $suiteData,
  ): void {
    $parser = $this->parserFromSuiteName($suiteData['name']);
    if ($parser->errors()->count() !== $suiteData['errors']) {
      var_dump($suiteData, $parser->errors());
    }
    $assert->int($parser->errors()->count())->eq($suiteData['errors']);
    $assert->int($parser->tests()->count())->eq(0);
  }
}
