<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

use HackPack\HackUnit\Contract\Assert;

class Test {
  <<Test>>
  public function defaultProvider(Assert $assert): void {}

  <<Test('named')>>
  public function namedProvider(Assert $assert): void {}

  <<Test>>
  public static function staticTest(Assert $assert): void {}

  <<Test, Skip>>
  public static function skippedTest(Assert $assert): void {}
}
