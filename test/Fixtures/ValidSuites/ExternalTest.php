<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

use HackPack\HackUnit\Contract\Assert;

class HasExternalSuite extends BaseSuite {
  use TestTrait;
}

trait TestTrait {
  <<Test>>
  public function testInsideTrait(Assert $assert): void {}
}

abstract class AbstractSuite {
  <<Test>>
  public function testInsideAbstractSuite(Assert $assert): void {}
}

class BaseSuite extends AbstractSuite {
  <<Test>>
  public function testInsideBaseSuite(Assert $assert): void {}
}
