<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuites;

use HackPack\HackUnit\Contract\Assert;

class AsyncData {

  <<DataProvider('async')>>
  public static async function asyncDataProvider(): AsyncIterator<int> {
    yield 1;
  }

  <<Test, Data('async')>>
  public function asyncConsumer(Assert $assert, int $value): void {}
}
