<?hh //strict
namespace HackUnit\Loading;

use HackUnit\Core\TestCase;

class ConventionalLoaderTest extends TestCase
{
    protected ?ConventionalLoader $loader;

    <<Override>> public function setUp(): void
    {
        $path = __DIR__ . '/../../../test/fixtures/loading';
        $this->loader = new ConventionalLoader($path);
    }

    public function test_load_should_return_classes_ending_in_Test_for_every_method(): void
    {
        if (! $this->loader) throw new \Exception("loader cannot be null");
        $pattern = '/Test$/';
        $objects = $this->loader->load();
        $this->expect($objects->count())->toEqual(4);
    }
}
