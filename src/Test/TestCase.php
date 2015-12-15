<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;

final class TestCase implements \HackPack\HackUnit\Contract\Test\TestCase
{
    public static function build(
        (function(Assert):Awaitable<void>) $test,
        Vector<(function():Awaitable<void>)> $setup,
        Vector<(function():Awaitable<void>)> $teardown,
    ) : this
    {
        return new static($test, $setup, $teardown);
    }

    public function __construct(
        private (function(Assert):Awaitable<void>) $test,
        private Vector<(function():Awaitable<void>)> $setup,
        private Vector<(function():Awaitable<void>)> $teardown,
    )
    {
    }

    public async function setup() : Awaitable<void>
    {
        await \HH\Asio\v($this->setup->map($f ==> $f()));
    }

    public async function teardown() : Awaitable<void>
    {
        await \HH\Asio\v($this->teardown->map($f ==> $f()));
    }

    public async function run(Assert $assert) : Awaitable<void>
    {
        $f = $this->test;
        await $f($assert);
    }
}
