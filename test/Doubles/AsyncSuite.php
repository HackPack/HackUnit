<?hh // strict

namespace HackPack\HackUnit\Tests\Doubles;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\Suite;

class AsyncSuite implements Suite
{
    public function __construct(private int $sleepTime) {  }

    public async function up() : Awaitable<void> { }
    public async function down() : Awaitable<void> { }

    public async function run(Assert $assert, (function():void) $testPassed) : Awaitable<void>
    {
        await \HH\Asio\usleep($this->sleepTime);
    }
}
