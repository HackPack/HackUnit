<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

use HackPack\HackUnit\Contract\Assert;

class TestConstructDestruct {
  <<Test>>
  public function __construct(Assert $assert) {}

  <<Test>>
  public function __destruct() {}
}

class TestParams {
  <<Test>>
  public function noParams(): void {}

  <<Test>>
  public function wrongParam(int $wrong): void {}

  <<Test>>
  public function tooManyParams(Assert $assert, int $wrong): void {}
}
