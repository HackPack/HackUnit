<?hh // strict

namespace HackPack\HackUnit\Tests\Mocks\Test;

use HackPack\HackUnit\Contract\Assert;


class TestCase implements \HackPack\HackUnit\Contract\Test\TestCase
{
    public ?Assert $assert = null;
    public Vector<(function():void)> $setup = Vector{};
    public Vector<(function():void)> $teardown = Vector{};

    public function run(Assert $assert) : void
    {
        $this->assert = $assert;
    }

    public function setup() : void
    {
        foreach($this->setup as $f) {
            $f();
        }
    }

    public function teardown() : void
    {
        foreach($this->teardown as $f) {
            $f();
        }
    }
}
