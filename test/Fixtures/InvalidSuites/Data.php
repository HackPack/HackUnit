<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

use HackPack\HackUnit\Contract\Assert;

class DataProviderMissingName {
  <<DataProvider>>
  public static function provider(): Traversable<string> {
    return [];
  }
}

class DataProviderWithParams {
  <<DataProvider('required params')>>
  public static function provider(int $i): Traversable<int> {
    return [];
  }
}

class InstanceDataProvider {
  <<DataProvider('instance provider')>>
  public function provider(int $i): Traversable<int> {
    return [];
  }
}

class NonTraversableDataProvider {
  <<DataProvider('void return')>>
  public static function provider(): void {}

  <<DataProvider('scalar')>>
  public static function intProvider(): int {
    return 1;
  }

  <<DataProvider('subclass')>>
  public static function vectorProvider(): Vector<int> {
    return Vector {};
  }
}

class DataConsumerMismatchParams {
  <<DataProvider('ints')>>
  public static function provider(): Traversable<int> {
    return [];
  }

  <<Test, Data('ints')>>
  public function consumer(Assert $assert, string $value): void {}
}

class DataConsumerMismatchGeneric {
  <<DataProvider('map')>>
  public static function mapProvider(): Traversable<Map<int, string>> {
    return Map {};
  }

  <<Test, Data('map')>>
  public static function consumesMap(
    Assert $assert,
    Map<arraykey, string> $data,
  ): void {}

  <<Test, Data('map')>>
  public static function consumesMapAgain(
    Assert $assert,
    Map<int, arraykey> $data,
  ): void {}
}

class DataConsumerMissingName {
  <<Test, Data>>
  public function consumer(Assert $assert): void {}
}
