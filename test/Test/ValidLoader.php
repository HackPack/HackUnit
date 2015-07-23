<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\MalformedSuite;
use HackPack\HackUnit\Test\Loader;
use HackPack\HackUnit\Tests\Mocks\Test\Suite;

<<TestSuite>>
class ValidLoaderTest
{
    private Vector<MalformedSuite> $malformedEvents = Vector{};
    private (function(\ReflectionClass):Suite) $suiteBuilder;
    private Loader $loader;
    private string $fixturesPath;

    public function __construct()
    {
        $this->suiteBuilder = $mirror ==> new Suite($mirror);
        $this->loader = new Loader($this->suiteBuilder);
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
        $this->malformedEvents->clear();
    }

    <<Test>>
    public function loadOneValidSuite(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('ValidSuite/ValidSuite.php'));
        $suites = $this->loader->testSuites();

        $assert->int($this->malformedEvents->count())->eq(0);
        $assert->int($suites->count())->eq(1);
        $suite = $suites->at(0);

        // Make sure we are getting the test double
        $assert->mixed($suite)->isTypeOf(Suite::class);
        invariant($suite instanceof Suite, '');

        $mirror = $suite->mirror;
        $assert->string($mirror->getName())->is($this->suiteName('ValidSuite\ValidSuite'));
        $assert->string((string)$mirror->getFileName())->is($this->suitePath('ValidSuite/ValidSuite.php'));
    }

    <<Test>>
    public function loadAllValidSuites(Assert $assert) : void
    {
        $this->loader->including($this->suitePath('ValidSuite'));
        $suites = $this->loader->testSuites();

        $assert->int($suites->count())->eq(6);
    }

    <<Test>>
    public function ignoreDirectory(Assert $assert) : void
    {
        $this->loader
            ->including($this->suitePath('ValidSuite'))
            ->excluding($this->suitePath('ValidSuite/IgnoreMe'))
            ;
        $suites = $this->loader->testSuites();

        $assert->int($this->malformedEvents->count())->eq(0);
        $assert->int($suites->count())->eq(3);
    }

    <<Test>>
    public function ignoreFileInBaseDirectory(Assert $assert) : void
    {
        $this->loader
            ->including($this->suitePath('ValidSuite'))
            ->excluding($this->suitePath('ValidSuite/ValidSuite.php'))
            ;
        $suites = $this->loader->testSuites();

        $assert->int($this->malformedEvents->count())->eq(0);
        $assert->int($suites->count())->eq(5);
    }

    <<Test>>
    public function ignoreFileInSubDirectory(Assert $assert) : void
    {
        $this->loader
            ->including($this->suitePath('ValidSuite'))
            ->excluding($this->suitePath('ValidSuite/IgnoreMe/ValidSuite.php'))
            ;
        $suites = $this->loader->testSuites();

        $assert->int($this->malformedEvents->count())->eq(0);
        $assert->int($suites->count())->eq(5);
    }
}
