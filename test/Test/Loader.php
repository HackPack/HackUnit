<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Loader;

class LoaderTest
{
    private string $validSuiteDir;

    private Vector<string> $fileNames = Vector{};

    private Loader $loader;

    public function __construct()
    {
        $this->validSuiteDir = dirname(__DIR__) . '/Fixtures/ValidSuite';
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
            ->including($this->validSuiteDir)
            ->excluding($this->validSuiteDir . '/IgnoreMe/ValidSuite.php')
            ->excluding($this->validSuiteDir . '/IgnoreMe/ValidSuite1.php')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->validSuiteDir . '/ValidSuite.php',
            $this->validSuiteDir . '/ValidSuite1.php',
            $this->validSuiteDir . '/ValidSuite2.php',
            $this->validSuiteDir . '/IgnoreMe/ValidSuite2.php',
        });
    }

    <<Test>>
    public function ignoresFile(Assert $assert) : void
    {
        $this
            ->loader
            ->including($this->validSuiteDir)
            ->excluding($this->validSuiteDir . '/IgnoreMe/ValidSuite.php')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->validSuiteDir . '/ValidSuite.php',
            $this->validSuiteDir . '/ValidSuite1.php',
            $this->validSuiteDir . '/ValidSuite2.php',
            $this->validSuiteDir . '/IgnoreMe/ValidSuite1.php',
            $this->validSuiteDir . '/IgnoreMe/ValidSuite2.php',
        });
    }

    <<Test>>
    public function ignoresFolder(Assert $assert) : void
    {
        $this
            ->loader
            ->including($this->validSuiteDir)
            ->excluding($this->validSuiteDir . '/IgnoreMe')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->validSuiteDir . '/ValidSuite.php',
            $this->validSuiteDir . '/ValidSuite1.php',
            $this->validSuiteDir . '/ValidSuite2.php',
        });
    }

    <<Test>>
    public function loadsAllFiles(Assert $assert) : void
    {
        $this->loader->including($this->validSuiteDir);
        $this->assertExpectedFiles($assert, Vector{
            $this->validSuiteDir . '/ValidSuite.php',
            $this->validSuiteDir . '/ValidSuite1.php',
            $this->validSuiteDir . '/ValidSuite2.php',
            $this->validSuiteDir . '/IgnoreMe/ValidSuite.php',
            $this->validSuiteDir . '/IgnoreMe/ValidSuite1.php',
            $this->validSuiteDir . '/IgnoreMe/ValidSuite2.php',
        });
    }

    <<Test>>
    public function loadsSingleFile(Assert $assert) : void
    {
        $this->loader->including($this->validSuiteDir . '/ValidSuite.php');
        $this->assertExpectedFiles($assert, Vector{
            $this->validSuiteDir . '/ValidSuite.php',
        });
    }

    <<Test>>
    public function loadsMultipleFiles(Assert $assert) : void
    {
        $this->loader
            ->including($this->validSuiteDir . '/ValidSuite.php')
            ->including($this->validSuiteDir . '/ValidSuite1.php')
        ;
        $this->assertExpectedFiles($assert, Vector{
            $this->validSuiteDir . '/ValidSuite.php',
            $this->validSuiteDir . '/ValidSuite1.php',
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
