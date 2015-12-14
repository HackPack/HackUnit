<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Contract\Assert;

interface TestCase
{
    public function setup() : void;
    public function teardown() : void;
    public function run(Assert $assert) : Awaitable<void>;
}
