<?hh //strict
namespace HackPack\HackUnit\Runner\Loading;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Core\TestGroup;
use HackPack\HackUnit\Core\TestSuite;
use HackPack\HackUnit\Runner\Options;
use kilahm\Scanner\ClassScanner;
use ReflectionClass;
use ReflectionMethod;

<<__ConsistentConstruct>>
class StandardLoader implements LoaderInterface
{
    private static string $methodNamePattern = '/^test/';
    private static string $attributeName = 'test';

    private ClassScanner $scanner;

    public static function create(Options $options): this
    {
        return new static($options->getIncludedPaths(), $options->getExcludedPaths());
    }

    public function __construct(Set<string> $include, Set<string> $exclude = Set {})
    {
        $this->scanner = new ClassScanner($include, $exclude);
    }

    public function loadSuite(): TestSuite
    {
        return new TestSuite($this->scanner->mapClassToFile()
            ->mapWithKey(($class, $path) ==> $this->loadTestGroup($class, $path))
            ->toVector()
        );
    }

    protected function loadTestGroup(string $classname, string $filename): ?TestGroup
    {
        $this->includeClass($filename);
        $classMirror = new ReflectionClass($classname);

        // Only subclasses of TestCase
        if(! $classMirror->isSubclassOf(TestCase::class)) {
            return null;
        }

        // Only methods named or attributed correctly
        $methods = Vector::fromItems($classMirror->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter($method ==>
                preg_match(self::$methodNamePattern, $method->getName()) ||
                $method->getAttributeRecursive(self::$attributeName) !== null
        );

        // Instance to attach all of the closures to
        $instance = $classMirror->newInstance();

        return shape(
            'start' => $classMirror->getMethod('start')->getClosure($instance),
            'setup' => $classMirror->getMethod('setUp')->getClosure($instance),
            'tests' => $methods->map($method ==> $method->getClosure($instance)),
            'teardown' => $classMirror->getMethod('tearDown')->getClosure($instance),
            'end' => $classMirror->getMethod('end')->getClosure($instance),
        );
    }

    protected function includeClass(string $testPath): void
    {
        /* HH_FIXME[1002] */
        require_once($testPath);
    }
}
