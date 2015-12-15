<?hh // strict

namespace HackPack\HackUnit\Tests\Mocks\Test;

use HackPack\HackUnit\Contract\Assert;


class TestCase implements \HackPack\HackUnit\Contract\Test\TestCase
{
    public ?Assert $assert = null;
    public Vector<(function():Awaitable<void>)> $setup = Vector{};
    public Vector<(function():Awaitable<void>)> $teardown = Vector{};

    public async function run(Assert $assert) : Awaitable<void>
    {
        $this->assert = $assert;
    }

    public async function setup() : Awaitable<void>
    {
        await \HH\Asio\v($this->setup->map($f ==> $f()));
    }

    public async function teardown() : Awaitable<void>
    {
        await \HH\Asio\v($this->teardown->map($f ==> $f()));
    }
}
