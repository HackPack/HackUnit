<?hh //strict
namespace HackUnit\Runner\Loading;

use HackUnit\Core\TestCase;
use HackUnit\Core\TestSuite;
use HackUnit\Runner\Options;

class StandardLoader implements LoaderInterface
{
    private static string $testPattern = '/Test.php$/';
    private static string $testMethodPattern = '/^test/';

    private Vector<TestCase> $testCases;
    private Instantiator $instantiator;

    public function __construct(protected string $path, protected Set<string> $exclude = Set {})
    {
        $this->testCases = Vector {};
        $this->exclude = $this->exclude->map(fun('realpath'));
        $this->instantiator = new Instantiator();
    }

    public function loadSuite(): TestSuite
    {
        $testCases = $this->load();
        $suite = new TestSuite();
        foreach ($testCases as $testCase) {
            $suite->add($testCase);
        }
        return $suite;
    }

    public function load(): Vector<TestCase>
    {
        $paths = $this->getTestCasePaths();
        foreach ($paths as $path) {
            $this->addTestCase($path);
        }
        return $this->testCases;
    }

    public function getTestCasePaths(string $searchPath = '', Set<string> $accum = Set {}): Set<string>
    {
        $searchPath = $searchPath ? $searchPath : $this->path;
        $files = scandir($searchPath);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $newPath = $searchPath . "/" . (string)$file;

            if ($this->isExcluded($newPath)) {
                continue;
            }

            if (is_file($newPath)) {
                if (! preg_match(StandardLoader::$testPattern, $newPath)) continue;
                $accum->add($newPath);
                continue;
            }

            $this->getTestCasePaths($newPath, $accum);
        }
        return $accum;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public static function create(Options $options): StandardLoader
    {
        return new static((string) $options->getTestPath(), $options->getExcludedPaths());
    }

    protected function isExcluded(string $path): bool
    {
        return $this->exclude->contains(realpath($path));
    }

    protected function addTestCase(string $testPath): void
    {
        $this->includeClass($testPath);
        $testCase = $this->instantiator->fromFile($testPath, ['noop']);
        $methods = get_class_methods($testCase);
        foreach ($methods as $method) {
            if (preg_match(StandardLoader::$testMethodPattern, $method)) {
                $test = $this->instantiator->fromObject($testCase, [$method]);
                $this->testCases->add($test);
            }
        }
    }

    protected function includeClass(string $testPath): void
    {
        // UNSAFE
        include_once($testPath);
    }
}
