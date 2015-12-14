<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Assert as AssertImpl;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test;
use HackPack\HackUnit\Tests\Fixtures\AsyncSuite;

class AsyncTest
{
    private static bool $started1 = false;
    private static bool $started2 = false;

    <<Test>>
    public async function asyncTest1(Assert $assert) : Awaitable<void>
    {
        self::$started1 = true;
        await \HH\Asio\later();
        $assert->bool(self::$started2)->is(true);
    }

    <<Test>>
    public async function asyncTest2(Assert $assert) : Awaitable<void>
    {
        self::$started2 = true;
        await \HH\Asio\later();
        $assert->bool(self::$started1)->is(true);
    }

    <<Test>>
    public function asyncTestTiming(Assert $assert) : void
    {
        $mirror = new \ReflectionClass(self::class);
        $suite = new Test\Suite($mirror, class_meth(Test\TestCase::class, 'build'));

        // Register sleeper method twice.
        // Series execution should be 4000 micro second sleep time
        $suite->registerTestMethod($mirror->getMethod('sleeper'));
        $suite->registerTestMethod($mirror->getMethod('sleeper'));

        $runner = new Test\Runner(class_meth(AssertImpl::class, 'build'));

        $start = microtime(true);
        $runner->run(Vector{$suite});
        $runTime = microtime(true) - $start;

        $expectedTime = 0.002; // 2000 micro seconds = time for one sleep
        $tolerance = 0.0005; // Actual time should be within 500 microseconds of one sleep

        $assert->float($runTime)->gt($expectedTime);
        $assert->float($runTime)->lt($expectedTime + $tolerance);
    }

    public async function sleeper(Assert $assert) : Awaitable<void>
    {
        await \HH\Asio\usleep(2000);
    }
}
