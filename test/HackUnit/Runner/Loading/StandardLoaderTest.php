<?hh //strict
namespace HackUnit\Runner\Loading;

use HackUnit\Core\TestCase;
use HackUnit\Runner\Options;

require_once __DIR__ . '/../../../../test/fixtures/loading/excluded/ThreeTest.php';

class StandardLoaderTest extends TestCase
{
    protected ?StandardLoader $loader;
    protected string $path = '';

    <<Override>> public function setUp(): void
    {
        $this->path = __DIR__ . '/../../../../test/fixtures/loading';
        $this->loader = new StandardLoader($this->path);
    }

    public function test_getTestCasePaths_should_return_paths_to_test_cases(): void
    {
        if (! $this->loader) throw new \Exception("loader and path cannot be null");
        $paths = $this->loader->getTestCasePaths();
        $this->expect($paths->count())->toEqual(3);
        $this->expect($paths->contains($this->path . '/OneTest.php'))->toEqual(true);
        $this->expect($paths->contains($this->path . '/TwoTest.php'))->toEqual(true);
        $this->expect($paths->contains($this->path . '/excluded/ThreeTest.php'))->toEqual(true);
    }

    public function test_getTestCasePaths_should_return_paths_with_single_file(): void
    {
        $loader = new StandardLoader($this->path . '/OneTest.php');
        $paths = $loader->getTestCasePaths();
        $this->expect($paths->count())->toEqual(1);
        $this->expect($paths->contains($this->path . '/OneTest.php'))->toEqual(true);
    }

    public function test_load_should_return_classes_ending_in_Test_for_every_method(): void
    {
        if (! $this->loader) throw new \Exception("loader cannot be null");
        $pattern = '/Test$/';
        $objects = $this->loader->load();
        $this->expect($objects->count())->toEqual(6);

        $oneTest = $objects->at(0);
        $oneTest2 = $objects->at(1);
        $this->expect($oneTest->getName())->toEqual('testOne');
        $this->expect($oneTest2->getName())->toEqual('testTwo');

        $twoTest = $objects->at(2);
        $twoTest2 = $objects->at(3);
        $this->expect($twoTest->getName())->toEqual('testThree');
        $this->expect($twoTest2->getName())->toEqual('testFour');

        $threeTest = $objects->at(4);
        $threeTest2 = $objects->at(5);
        $this->expect($threeTest->getName())->toEqual('testFive');
        $this->expect($threeTest2->getName())->toEqual('testSix');
    }
    
    public function test_loadSuite_should_use_results_of_load_to_create_a_TestSuite(): void
    {
        if (! $this->loader) throw new \Exception("loader cannot be null");
        $suite = $this->loader->loadSuite();
        $tests = $suite->getTests();
        $this->expect($tests->count())->toEqual(6);
    }

    public function test_getTestCasePaths_should_exclude_dirs(): void
    {
        $options = new Options();
        $options
            ->setTestPath($this->path)
            ->setExcludedPaths($this->path . '/excluded');
        $loader = StandardLoader::create($options);
        $paths = $loader->getTestCasePaths();
        $this->expect($paths->count())->toEqual(2);
    }
}
