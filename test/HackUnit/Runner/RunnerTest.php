<?hh //strict
namespace HackUnit\Runner;

use HackUnit\Core\TestCase;
use HackUnit\Runner\Loading\ConventionalLoader;

class RunnerTest extends TestCase
{
    public function test_runner_constructs_loader_via_factory_using_options(): void
    {
        $options = new Options();
        $options->setTestPath(__DIR__);
        $runner = new Runner($options, ($opts) ==> new ConventionalLoader((string)$opts->getTestPath()));

        $loader = $runner->getLoader();

        $this->expect($loader->getPath())->toEqual(__DIR__);
    }
}
