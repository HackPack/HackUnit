<?hh //strict
namespace HackPack\HackUnit\Tests\Runner;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Runner\Loading\StandardLoader;
use HackPack\HackUnit\Runner\Runner;
use HackPack\HackUnit\Runner\Options;
use HackPack\HackUnit\UI\NullReporter;

class RunnerTest extends TestCase
{
    <<test>>
    public function test_run_returns_result_from_loader(): void
    {
        $options = new Options();
        $options->addIncludePath(__DIR__ . '/../Fixtures/Loading');
        $result = new TestResult();
        $runner = new Runner(
            NullReporter::create(),
            $options,
            StandardLoader::create($options),
            $result,
        );

        $runner->run();

        $this->expect($result->testCount())->toEqual(6);
    }

    <<test>>
    public function test_run_returns_result_with_a_started_timer(): void
    {
        $options = new Options();
        $options->addIncludePath(__DIR__ . '/../Fixtures/Loading');
        $result = new TestResult();
        $runner = new Runner(
            NullReporter::create(),
            $options,
            StandardLoader::create($options),
            $result,
        );

        $runner->run();
        $this->expect(is_null($result->getStartTime()))->toEqual(false);
    }
}
