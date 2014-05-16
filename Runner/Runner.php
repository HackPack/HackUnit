<?hh //strict
namespace HackPack\HackUnit\Runner;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Runner\Loading\LoaderInterface;

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
        $result->startTimer();
        $suite = $this->getLoader()->loadSuite();
        $suite->run($result);
        return $result;
    }
}
