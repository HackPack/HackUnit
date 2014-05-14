<?hh //strict
namespace HackUnit\Runner;

use HackUnit\Core\TestCase;
use HackUnit\Runner\Loading\StandardLoader;

class RunnerTest extends TestCase
{
    protected ?(function(Options): StandardLoader) $factory;

    <<Override>> public function setUp(): void
    {
        $this->factory = class_meth('\HackUnit\Runner\Loading\StandardLoader', 'create');
    }

    public function test_runner_constructs_loader_via_factory_using_options(): void
    {
        $options = new Options();
        $options->setTestPath(__DIR__);
        $runner = new Runner($options, $this->factory ?: ($opts) ==> new StandardLoader('null'));

        $loader = $runner->getLoader();

        $this->expect($loader->getPath())->toEqual(__DIR__);
    }

    public function test_run_returns_result_from_loader(): void
    {
        $factory = $this->factory ?: ($opts) ==> new StandardLoader('null');
        $options = new Options();
        $options->setTestPath(__DIR__ . '/../../fixtures/loading');
        $runner = new Runner($options, $factory);

        $result = $runner->run();

        $this->expect($result->getTestCount())->toEqual(6);
    }
}
