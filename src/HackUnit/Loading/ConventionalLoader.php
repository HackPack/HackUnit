<?hh //strict
namespace HackUnit\Loading;

use HackUnit\Core\TestCase;

class ConventionalLoader
{
    private static string $testPattern = '/Test.php$/';
    private static string $testMethodPattern = '/^test/';

    private Vector<TestCase> $testCases;

    public function __construct(protected string $path)
    {
        $this->testCases = Vector {};
    }

    public function load(): Vector<TestCase>
    {
        $this->buildTestCases($this->path);
        return $this->testCases;
    }

    protected function buildTestCases(string $path): void
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $newPath = $path . "/" . (string)$file;
            if (is_file($newPath)) {
                $this->addTestCase($newPath);
                continue;
            }
            $this->buildTestCases($newPath);
        }
    }

    protected function addTestCase(string $testPath): void
    {
        if (! preg_match(ConventionalLoader::$testPattern, $testPath)) return;
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
