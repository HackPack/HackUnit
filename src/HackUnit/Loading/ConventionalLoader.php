<?hh //strict
namespace HackUnit\Loading;

use HackUnit\Core\TestCase;
use HackUnit\Core\TestSuite;

class ConventionalLoader implements LoaderInterface
{
    private static string $testPattern = '/Test.php$/';
    private static string $testMethodPattern = '/^test/';

    private Vector<TestCase> $testCases;

    public function __construct(protected string $path, protected Vector<string> $exclude = Vector {})
    {
        $this->testCases = Vector {};
        $this->exclude = $this->exclude->map(fun('realpath'));
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

    public function getTestCasePaths(string $searchPath = '', Vector<string> $accum = Vector {}): Vector<string>
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
                if (! preg_match(ConventionalLoader::$testPattern, $newPath)) continue;
                $accum->add($newPath);
                continue;
            }

            $this->getTestCasePaths($newPath, $accum);
        }
        return $accum;
    }

    protected function isExcluded(string $path): bool
    {
        return $this->exclude->linearSearch(realpath($path)) != -1;
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
        $fp = fopen($testPath, 'r');
        $namespace = $class = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;
            $buffer .= (string) fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (; $i < count($tokens); $i++) {

                if ($tokens[$i][0] === 377) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === 307) {
                            $namespace .= '\\' . (string) $tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === 353) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i +2][1];
                        }
                    }
                }
            }
        }
        $className = $namespace . '\\' . $class;
        return hphp_create_object($className, [$testMethod]);
    }

    protected function includeClass(string $testPath): void
    {
        // UNSAFE
        include_once($testPath);
    }
}
