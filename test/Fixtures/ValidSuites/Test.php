<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

use HackPack\HackUnit\Contract\Assert;

final class Test {
  <<SuiteProvider('named')>>
  public static function namedBuilder(): this {
    return new static();
  }

  <<Test>>
  public function defaultSuiteProvider(Assert $assert): void {}

  <<Test('named')>>
  public function namedSuiteProvider(Assert $assert): void {}

  <<Test>>
  public static function staticTest(Assert $assert): void {}

  <<Test, Skip>>
  public static function skippedTest(Assert $assert): void {}
}
