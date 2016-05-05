<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

use HackPack\HackUnit\Contract\Assert;

class Data {
  <<Test, Data('vector')>>
  public static function consumesVector(
    Assert $assert,
    Vector<string> $data,
  ): void {}

  <<Test, Data('map')>>
  public static function consumesMap(
    Assert $assert,
    Map<int, string> $data,
  ): void {}

  <<Test, Data('string')>>
  public static function consumesString(Assert $assert, string $data): void {}

  <<Data('string')>>
  public static function notRecognized(Assert $assert): void {}

  <<DataProvider('vector')>>
  public static function vectorProvider(): Traversable<Vector<string>> {
    return Vector {};
  }

  <<DataProvider('map')>>
  public static function mapProvider(): Traversable<Map<int, string>> {
    return Map {};
  }

  <<DataProvider('string')>>
  public static function stringProvider(): Traversable<string> {
    return Map {};
  }

  <<DataProvider('with parameter')>>
  public static function dataProviderWithParam(int $i = 1): Traversable<int> {
    return [];
  }
}
