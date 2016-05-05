<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;

class AsyncTest {
  private static Set<string> $finished = Set {};

  <<Test>>
  public async function testOne(Assert $assert): Awaitable<void> {
    $assert->container(self::$finished)->isEmpty();
    await \HH\Asio\later();
    self::$finished->add('testOne');
  }

  <<Test>>
  public async function testTwo(Assert $assert): Awaitable<void> {
    $assert->container(self::$finished)->isEmpty();
    await \HH\Asio\later();
    self::$finished->add('testTwo');
  }

  <<DataProvider('async')>>
  public static async function threeValues(): AsyncIterator<string> {
    yield '1';
    yield '2';
    yield '3';
  }

  <<Test, Data('async')>>
  public async function dataConsumer(
    Assert $assert,
    string $value,
  ): Awaitable<void> {
    $assert->container(self::$finished)->isEmpty();
    await \HH\Asio\later();
    self::$finished->add($value);
  }

}
