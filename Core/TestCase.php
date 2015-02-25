<?hh //strict
namespace HackPack\HackUnit\Core;

use HackPack\HackUnit\Exception\MarkTestAsSkipped;

<<__ConsistentConstruct>>
abstract class TestCase implements TestInterface
{
    public function __construct()
    {
    }

    public function start(): void
    {
    }

    public function end(): void
    {
    }

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function expect<T>(T $context): Expectation<T>
    {
        return new Expectation($context);
    }

    public function expectCallable((function(): void) $callable): CallableExpectation
    {
        return new CallableExpectation($callable);
    }

    public function markAsSkipped(string $message = "Skipped"): void
    {
        throw new MarkTestAsSkipped($message);
    }
}
