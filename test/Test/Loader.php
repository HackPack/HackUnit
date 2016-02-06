<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Loader;

class LoaderTest
{
    private string $fileDir;

    private Vector<string> $fileNames = Vector{};

    private Loader $loader;

    public function __construct()
    {
        $this->fileDir = dirname(__DIR__) . '/Fixtures/LoadingFiles';
        $this->loader = new Loader(
            $filename ==> {
                $this->fileNames->add($filename);
                return Vector{};
            }
        );
    }

    <<Test>>
    public function ignoresMultipleFiles(Assert $assert) : void
    {
        $this
            ->loader
            ->including($this->fileDir)
            ->excluding($this->fileDir . '/IgnoreMe/file1')
            ->excluding($this->fileDir . '/IgnoreMe/file2')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->fileDir . '/file1',
            $this->fileDir . '/file2',
            $this->fileDir . '/file3',
            $this->fileDir . '/IgnoreMe/file3',
        });
    }

    <<Test>>
    public function ignoresFile(Assert $assert) : void
    {
        $this
            ->loader
            ->including($this->fileDir)
            ->excluding($this->fileDir . '/IgnoreMe/file1')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->fileDir . '/file1',
            $this->fileDir . '/file2',
            $this->fileDir . '/file3',
            $this->fileDir . '/IgnoreMe/file2',
            $this->fileDir . '/IgnoreMe/file3',
        });
    }

    <<Test>>
    public function ignoresFolder(Assert $assert) : void
    {
        $this
            ->loader
            ->including($this->fileDir)
            ->excluding($this->fileDir . '/IgnoreMe')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->fileDir . '/file1',
            $this->fileDir . '/file2',
            $this->fileDir . '/file3',
        });
    }

    <<Test>>
    public function loadsAllFiles(Assert $assert) : void
    {
        $this->loader->including($this->fileDir);
        $this->assertExpectedFiles($assert, Vector{
            $this->fileDir . '/file1',
            $this->fileDir . '/file2',
            $this->fileDir . '/file3',
            $this->fileDir . '/IgnoreMe/file1',
            $this->fileDir . '/IgnoreMe/file2',
            $this->fileDir . '/IgnoreMe/file3',
        });
    }

    <<Test>>
    public function loadsSingleFile(Assert $assert) : void
    {
        $this->loader->including($this->fileDir . '/file1');
        $this->assertExpectedFiles($assert, Vector{
            $this->fileDir . '/file1',
        });
    }

    <<Test>>
    public function loadsMultipleFiles(Assert $assert) : void
    {
        $this->loader
            ->including($this->fileDir . '/file1')
            ->including($this->fileDir . '/file2')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->fileDir . '/file1',
            $this->fileDir . '/file2',
        });
    }

    private function assertExpectedFiles(Assert $assert, Vector<string> $expectedFiles) : void
    {
        // This actually hits the filesystem
        $this->loader->testSuites();

        $missing = array_diff($expectedFiles, $this->fileNames);
        $extra = array_diff($this->fileNames, $expectedFiles);

        $assert->int(count($missing))->eq(0);
        $assert->int(count($extra))->eq(0);
    }
}
