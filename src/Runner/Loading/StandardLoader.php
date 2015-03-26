<?hh //strict
namespace HackPack\HackUnit\Runner\Loading;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Core\TestGroup;
use HackPack\HackUnit\Core\TestSuite;
use HackPack\HackUnit\Exception\MalformedSuite;
use HackPack\HackUnit\Runner\Options;
use HackPack\HackUnit\TestGroupAttribute;
use HackPack\Scanner\ClassScanner;
use ReflectionClass;
use ReflectionMethod;

<<__ConsistentConstruct>>
class StandardLoader implements LoaderInterface
{
    protected Set<string> $filesWithTests = Set{};
    protected Vector<TestGroup> $groups = Vector{};

    private ClassScanner $scanner;

    public static function create(Options $options): this
    {
        return new static($options->getIncludedPaths(), $options->getExcludedPaths());
    }

    public function __construct(Set<string> $include, Set<string> $exclude = Set {})
    {
        $this->scanner = new ClassScanner($include, $exclude);
    }

    public function getFilesWithTests() : Set<string>
    {
        return $this->filesWithTests;
    }

    public function loadSuite(): TestSuite
    {
        return new TestSuite($this->loadTests());
    }

    public function loadTests(): Vector<TestGroup>
    {
        $this->groups->clear();
        $this->filesWithTests->clear();
        foreach($this->scanner->mapClassToFile() as $class => $path) {
            $group = $this->loadTestGroup($class, $path);
            if($group !== null) {
                $this->filesWithTests->add($path);
                $this->groups->add($group);
            }
        }
        return $this->groups;
    }

    protected function loadTestGroup(string $classname, string $filename): ?TestGroup
    {
        $this->includeFile($filename);
        $classMirror = new ReflectionClass($classname);
        if( ! $classMirror->isSubclassOf(TestCase::class)) {
            return null;
        }

        $group = shape(
            'start' => Vector{},
            'setup' => Vector{},
            'tests' => Vector{},
            'teardown' => Vector{},
            'end' => Vector{},
        );

        $startendInstance = $classMirror->newInstance($classMirror->getMethod('markAsMalformed'));

        // Only methods named or attributed correctly
        foreach($classMirror->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

            if($method->getAttribute(TestGroupAttribute::start) !== null) {
                $group['start']->add(
                    $this->verifyMethodSignature($method)
                    ->getClosure($startendInstance)
                );
            }

            if($method->getAttribute(TestGroupAttribute::setup) !== null) {
                $group['setup']->add($this->verifyMethodSignature($method));
            }

            if($method->getAttribute(TestGroupAttribute::test) !== null) {
                $group['tests']->add(shape(
                    'instance' => $classMirror->newInstance(),
                    'method' => $this->verifyMethodSignature($method)
                ));
            }

            if($method->getAttribute(TestGroupAttribute::teardown) !== null) {
                $group['teardown']->add($this->verifyMethodSignature($method));
            }

            if($method->getAttribute(TestGroupAttribute::end) !== null) {
                $group['end']->add(
                    $this->verifyMethodSignature($method)
                    ->getClosure($startendInstance)
                );
            }
        }

        return $group['tests']->isEmpty() ? null : $group;
    }

    protected function verifyMethodSignature(ReflectionMethod $method) : ReflectionMethod
    {
        if(
            $method->getNumberOfParameters() !== 0 ||
            $method->getReturnTypeText() !== 'HH\void' ||
            $method->isStatic()
        ) {
            return $method->getDeclaringClass()->getMethod('markAsMalformed');
        }
        return $method;
    }

    protected function includeFile(string $testPath): void
    {
        /* HH_FIXME[1002] */
        require_once($testPath);
    }
}
