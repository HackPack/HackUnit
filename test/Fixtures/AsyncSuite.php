<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures;

use HackPack\HackUnit\Contract\Assert;

class AsyncSuite
{
    public async function testOne(Assert $assert) : Awaitable<void>
    {
        await \HH\Asio\usleep(2000);
    }

    public async function testTwo(Assert $assert) : Awaitable<void>
    {
        await \HH\Asio\usleep(2000);
    }
}
