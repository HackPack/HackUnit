<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

class SuiteUpParams {
  <<Setup('suite')>>
  public static function invalid(int $requiredParam): void {}
}

class SuiteUpNonStatic {
  <<Setup('suite')>>
  public function invalid(): void {}
}

class SuiteDownParams {
  <<TearDown('suite')>>
  public static function invalid(int $requiredParam): void {}
}

class SuiteDownNonStatic {
  <<TearDown('suite')>>
  public function invalid(): void {}
}
