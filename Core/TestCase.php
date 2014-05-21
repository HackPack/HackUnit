<?hh //strict
namespace HackPack\HackUnit\Core;

abstract class TestCase implements TestInterface
{
    public function __construct(protected string $name)
    {
    }

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function expect<T>(T $context): Expectation<T>
    {
        return new Expectation($context);
    }

    public function expectCallable((function(): void) $callable): CallableExpectation
    {
        return new CallableExpectation($callable);
    }

    public function run(TestResult $result): TestResult
    {
        $result->testStarted();
        $this->setUp();
        $class = get_class($this);
        try {
            hphp_invoke_method($this, $class, $this->name, []);
            $result->testPassed();
        } catch (\Exception $e) {
            $result->testFailed($e);
        }
        $this->tearDown();
        return $result;
    }
}
