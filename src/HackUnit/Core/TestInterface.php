<?hh //strict
namespace HackUnit\Core;

interface TestInterface
{
    public function run(TestResult $result): TestResult;
}
