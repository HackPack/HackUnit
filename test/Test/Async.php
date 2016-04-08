<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;

class AsyncTest {
  private static Set<string> $finished = Set {};

  <<Test>>
  public async function testOne(Assert $assert): Awaitable<void> {
    $assert->int(self::$finished->count())->eq(0);
    await $this->waitForMe();
    self::$finished->add('testOne');
  }

  <<Test>>
  public async function testTwo(Assert $assert): Awaitable<void> {
    $assert->int(self::$finished->count())->eq(0);
    await $this->waitForMe();
    self::$finished->add('testTwo');
  }

  private async function waitForMe(): Awaitable<void> {
    await \HH\Asio\usleep(1);
  }
}
