<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

class TearDown {
  <<TearDown('suite')>>
  public static function suiteOnly(): void {}

  <<TearDown('suite')>>
  public static function nonRequiredParam(int $param = 0): void {}

  <<TearDown('test', 'suite')>>
  public static function both(): void {}

  <<TearDown('test')>>
  public function testOnlyExplicit(): void {}

  <<TearDown>>
  public function testOnlyImplicit(): void {}
}
