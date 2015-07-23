<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Loader;
use HackPack\HackUnit\Tests\Mocks\Test\Suite;

<<TestSuite>>
class InvalidLoaderTest
{
    private Vector<MalformedSuite> $malformedEvents = Vector{};
    private (function(\ReflectionClass):Suite) $suiteBuilder;
    private Loader $loader;
    private string $fixturesPath;

    public function __construct()
    {
        $this->suiteBuilder = $mirror ==> new Suite($mirror);
        $this->loader = new Loader($this->suiteBuilder);
        $this->loader->onMalformedSuite($e ==> {$this->malformedEvents->add($e);});
        $this->fixturesPath = dirname(__DIR__) . '/Fixtures';
    }

    private function suitePath(string $path) : string
    {
        return $this->fixturesPath . '/' . $path;
    }

    private function suiteName(string $name) : string
    {
        return 'HackPack\HackUnit\Tests\Fixtures\\' . $name;
    }

    <<Setup>>
    public function resetLoader() : void
    {
        $this->loader = new Loader($this->suiteBuilder);
        $this->loader->onMalformedSuite($e ==> {$this->malformedEvents->add($e);});
        $this->malformedEvents->clear();
    }

    <<Test>>
    public function invalidSetupMethods(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('InvalidSuites/SuiteSetup'));
        $suites = $this->loader->testSuites();

        $assert->int($this->malformedEvents->count())->eq(4);
        $assert->int($suites->count())->eq(0);
    }

}
