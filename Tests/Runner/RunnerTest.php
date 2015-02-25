<?hh //strict
namespace HackPack\HackUnit\Tests\Runner;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Runner\Loading\StandardLoader;
use HackPack\HackUnit\Runner\Runner;
use HackPack\HackUnit\Runner\Options;

class RunnerTest extends TestCase
{
    <<test>>
    public function test_run_returns_result_from_loader(): void
    {
        $options = new Options();
        $options->setTestPath(__DIR__ . '/../Fixtures/Loading');
        $runner = new Runner($options, StandardLoader::create($options));

        $result = $runner->run();

        $this->expect($result->getTestCount())->toEqual(6);
    }

    <<test>>
    public function test_run_returns_result_with_a_started_timer(): void
    {
        $options = new Options();
        $options->setTestPath(__DIR__ . '/../Fixtures/Loading');
        $runner = new Runner($options, StandardLoader::create($options));

        $result = $runner->run();

        $this->expect(is_null($result->getTime()))->toEqual(false);
    }
}
