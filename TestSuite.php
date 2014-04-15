<?hh //strict
require_once 'TestCase.php';
require_once 'TestResult.php';
class TestSuite
{
    public function __construct(protected Vector<TestCase> $tests = Vector {})
    {
    }

    public function add(TestCase $case): void
    {
        $this->tests->add($case);
    }

    public function run(TestResult $result): TestResult
    {
        foreach ($this->tests as $test) {
            $test->run($result);
        }
        return $result;
    }
}
