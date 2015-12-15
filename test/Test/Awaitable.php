<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Assert as AssertImpl;
use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test;

class AsyncTest
{
    private static bool $started1 = false;
    private static bool $started2 = false;

    <<Test>>
    public async function awaitableTest1(Assert $assert) : Awaitable<void>
    {
        self::$started1 = true;
        await \HH\Asio\later();
        $assert->bool(self::$started2)->is(true);
    }

    <<Test>>
    public async function awaitableTest2(Assert $assert) : Awaitable<void>
    {
        self::$started2 = true;
        await \HH\Asio\later();
        $assert->bool(self::$started1)->is(true);
    }

    <<Test>>
    public function awaitableTestTiming(Assert $assert) : void
    {
        $mirror = new \ReflectionClass(self::class);
        $suite = new Test\Suite($mirror, class_meth(Test\TestCase::class, 'build'));
        $runner = new Test\Runner(class_meth(AssertImpl::class, 'build'));

        // Register sleeper method twice.
        // Series execution should be 4000 micro second sleep time
        $suite->registerTestMethod($mirror->getMethod('sleeper'));
        $suite->registerTestMethod($mirror->getMethod('sleeper'));

        $start = microtime(true);
        $runner->run(Vector{$suite});
        $runTime = microtime(true) - $start;

        $expectedTime = 0.002; // 2000 micro seconds = time for one sleep

        $assert->float($runTime)->gt($expectedTime);
        $assert->float($runTime)->lt(2 * $expectedTime);
    }

    <<Test>>
    public function awaitableSetUpAndTearDown(Assert $assert) : void
    {
        $mirror = new \ReflectionClass(self::class);
        $suite = new Test\Suite($mirror, class_meth(Test\TestCase::class, 'build'));
        $runner = new Test\Runner(class_meth(AssertImpl::class, 'build'));

        // Register sleeper method as setup and teardown
        $suite->registerTestSetup($mirror->getMethod('sleeperUpDown'));
        $suite->registerTestTeardown($mirror->getMethod('sleeperUpDown'));

        // Register sleeper method as three tests
        $suite->registerTestMethod($mirror->getMethod('sleeper'));
        $suite->registerTestMethod($mirror->getMethod('sleeper'));
        $suite->registerTestMethod($mirror->getMethod('sleeper'));

        // Execution should be three parallel paths of setup -> test -> teardown
        // If all sleeps are parallel, total sleep time should be ~6000 uSec
        // Ensure actual time is more than sleep time for one run
        // but less than sleep time for all runs
        $expectedTime = 0.006;

        $start = microtime(true);
        $runner->run(Vector{$suite});
        $runTime = microtime(true) - $start;

        $assert->float($runTime)->gt($expectedTime);
        $assert->float($runTime)->lt(3 * $expectedTime);
    }

    public async function sleeper(Assert $assert) : Awaitable<void>
    {
        await \HH\Asio\usleep(2000);
    }

    public async function sleeperUpDown() : Awaitable<void>
    {
        await \HH\Asio\usleep(2000);
    }
}
