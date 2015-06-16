<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;

final class TestCase implements \HackPack\HackUnit\Contract\Test\TestCase
{
    public static function build(
        (function(Assert):void) $test,
        Vector<(function():void)> $setup,
        Vector<(function():void)> $teardown,
    ) : this
    {
        return new static($test, $setup, $teardown);
    }

    public function __construct(
        private (function(Assert):void) $test,
        private Vector<(function():void)> $setup,
        private Vector<(function():void)> $teardown,
    )
    {
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

    public function run(Assert $assert) : void
    {
        $f = $this->test;
        $f($assert);
    }
}
