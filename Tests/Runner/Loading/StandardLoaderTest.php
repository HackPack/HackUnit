<?hh //strict
namespace HackPack\HackUnit\Tests\Runner\Loading;

use HackPack\HackUnit\Core\TestCase;
use HackPack\HackUnit\Runner\Options;
use HackPack\HackUnit\Runner\Loading\StandardLoader;

class StandardLoaderTest extends TestCase
{
    protected static Set<string> $includes = Set{};
    protected string $path = '';

    <<setUp>>
    public function setPath(): void
    {
        $this->path = dirname(dirname(__DIR__)) . '/Fixtures/Loading';
        self::$includes = Set{$this->path};
    }

    <<test>>
    public function test_getFilesWithTests_should_return_paths_to_test_cases(): void
    {
        $loader = new StandardLoader(self::$includes->toSet());
        $loader->loadTests();
        $paths = $loader->getFilesWithTests();
        $this->expect($paths->count())->toEqual(3);
        $this->expect($paths->contains($this->path . '/OneTest.hh'))->toEqual(true);
        $this->expect($paths->contains($this->path . '/TwoTest.php'))->toEqual(true);
        $this->expect($paths->contains($this->path . '/Excluded/Three.php'))->toEqual(true);
    }

    <<test>>
    public function test_getFilesWithTests_should_return_paths_with_single_file(): void
    {
        $loader = new StandardLoader(Set{$this->path . '/OneTest.hh'});
        $loader->loadTests();
        $paths = $loader->getFilesWithTests();
        $this->expect($paths->count())->toEqual(1);
        $this->expect($paths->contains($this->path . '/OneTest.hh'))->toEqual(true);
    }

    <<test>>
    public function test_loadTests_should_return_test_group_for_every_class_that_extends_TestCase(): void
    {
        $loader = new StandardLoader(self::$includes->toSet());
        $pattern = '/Test$/';
        $groups = $loader->loadTests();

        // Make sure the test files were included
        $this->expect(class_exists('\OneTest', false))->toEqual(true);
        $this->expect(class_exists('\TwoTest', false))->toEqual(true);
        $this->expect(class_exists('\ThreeTest', false))->toEqual(true);
        $this->expect(class_exists('\TestMeNot', false))->toEqual(true);

        $this->expect($groups->count())->toEqual(3);
        foreach($groups as $group) {
            switch(get_class($group['test']->at(0))) {
            case \OneTest::class:
                $this->expect($group['test']->at(1) instanceof \OneTest)->toEqual(true);
                $this->expect($group['test']->count())->toEqual(2);
                $this->expect($group['start']->count())->toEqual(0);
                $this->expect($group['end']->count())->toEqual(0);
                $this->expect($group['setup']->count())->toEqual(1);
                $this->expect($group['teardown']->count())->toEqual(0);
                break;

            case \TwoTest::class:
                $this->expect($group['test']->at(1) instanceof \TwoTest)->toEqual(true);
                $this->expect($group['test']->count())->toEqual(2);
                $this->expect($group['start']->count())->toEqual(0);
                $this->expect($group['end']->count())->toEqual(0);
                $this->expect($group['setup']->count())->toEqual(0);
                $this->expect($group['teardown']->count())->toEqual(1);
                break;

            case \ThreeTest::class:
                $this->expect($group['test']->at(1) instanceof \ThreeTest)->toEqual(true);
                $this->expect($group['test']->count())->toEqual(2);
                $this->expect($group['start']->count())->toEqual(1);
                $this->expect($group['end']->count())->toEqual(1);
                $this->expect($group['setup']->count())->toEqual(0);
                $this->expect($group['teardown']->count())->toEqual(0);
                break;

            default:
                throw new \Exception('Unexpected test class.');
            }
        }
    }

    <<test>>
    public function test_getFilesWithTests_should_exclude_dirs(): void
    {
        $options = new Options();
        $options
            ->setTestPath($this->path)
            ->addExcludedPath($this->path . '/Excluded');
        $loader = StandardLoader::create($options);
        $loader->loadTests();
        $paths = $loader->getFilesWithTests();
        $this->expect($paths->count())->toEqual(2);
    }

    <<test>>
    public function test_getFilesWithTests_should_exclude_nonexistent_dirs(): void
    {
        $options = new Options();
        $options
            ->setTestPath($this->path . '/DoesNotExist');
        $loader = StandardLoader::create($options);
        $loader->loadTests();
        $paths = $loader->getFilesWithTests();
        $this->expect($paths->count())->toEqual(0);
    }
}
