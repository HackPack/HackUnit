<?hh //strict
namespace HackUnit\Loading;

use HackUnit\Core\TestCase;
use HackUnit\Core\TestSuite;

class ConventionalLoader
{
    private static string $testPattern = '/Test.php$/';
    private static string $testMethodPattern = '/^test/';

    private Vector<TestCase> $testCases;

    public function __construct(protected string $path)
    {
        $this->testCases = Vector {};
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
        $paths = $this->getTestCasePaths($this->path);
        foreach ($paths as $path) {
            $this->addTestCase($path);
        }
        return $this->testCases;
    }

    public function getTestCasePaths(string $searchPath, Vector<string> $accum = Vector {}): Vector<string>
    {
        $files = scandir($searchPath);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $newPath = $searchPath . "/" . (string)$file;
            if (is_file($newPath)) {
                if (! preg_match(ConventionalLoader::$testPattern, $newPath)) continue;
                $accum->add($newPath);
                continue;
            }
            $this->getTestCasePaths($newPath, $accum);
        }
        return $accum;
    }

    protected function addTestCase(string $testPath): void
    {
        $this->includeClass($testPath);
        $testCase = $this->createTestCaseInstance($testPath, 'noop');
        $methods = get_class_methods($testCase);
        foreach ($methods as $method) {
            if (preg_match(ConventionalLoader::$testMethodPattern, $method)) {
                $test = $this->createTestCaseInstance($testPath, $method);
                $this->testCases->add($test);
            }
        }
    }

    protected function createTestCaseInstance(string $testPath, string $testMethod): TestCase
    {
        $classFile = substr($testPath, strlen($this->path) + 1, strlen($testPath));
        $className = str_replace('/', '\\', str_replace('.php', '', $classFile));
        return hphp_create_object($className, [$testMethod]);
    }

    protected function includeClass(string $testPath): void
    {
        // UNSAFE
        include_once($testPath);
    }
}
