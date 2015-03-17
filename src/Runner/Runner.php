<?hh //strict
namespace HackPack\HackUnit\Runner;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Runner\Loading\LoaderInterface;
use HackPack\Hacktions\EventEmitter;

class Runner<TLoader as LoaderInterface>
{
    use EventEmitter;

    public function __construct(protected Options $options, protected TLoader $loader)
    {
    }

    public function getLoader(): TLoader
    {
        return $this->loader;
    }

    public function run(): TestResult
    {
        $result = new TestResult();
        $result->on('testPassed', (...) ==> $this->trigger('testPassed'));
        $result->on('testFailed', (...) ==> $this->trigger('testFailed'));
        $result->on('testSkipped', (...) ==> $this->trigger('testSkipped'));
        $result->startTimer();

        $suite = $this->getLoader()->loadSuite();
        $suite->run($result);

        return $result;
    }
}
