<?hh //strict
namespace HackUnit\Runner;

use HackUnit\Core\TestResult;
use HackUnit\Runner\Loading\LoaderInterface;

class Runner<TLoader as LoaderInterface>
{
    protected TLoader $loader;

    public function __construct(protected Options $options, (function(Options): TLoader) $factory)
    {
        $this->loader = $factory($options);
    }

    public function getLoader(): TLoader
    {
        return $this->loader;
    }

    public function run(): TestResult
    {
        $result = new TestResult();
        $suite = $this->getLoader()->loadSuite();
        $suite->run($result);
        return $result;
    }
}
