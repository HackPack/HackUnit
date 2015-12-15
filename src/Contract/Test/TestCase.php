<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Contract\Assert;

interface TestCase
{
    public function setup() : Awaitable<void>;
    public function teardown() : Awaitable<void>;
    public function run(Assert $assert) : Awaitable<void>;
}
