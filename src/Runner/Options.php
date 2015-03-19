<?hh //strict
namespace HackPack\HackUnit\Runner;

use kilahm\Clio\Clio;

<<__ConsistentConstruct>> class Options
{
    protected Set<string> $includePaths = Set{};
    protected Set<string> $excludedPaths = Set{};
    protected string $hackUnitFile = '';

    public function addIncludePath(string $path): this
    {
        if($path !== '') {
            $this->includePaths->add($path);
        }
        return $this;
    }

    public function getIncludedPaths(): Set<string>
    {
        if($this->includePaths->isEmpty()) {
            return Set{getcwd()};
        }
        return $this->includePaths;
    }

    public function addExcludedPath(string $path): this
    {
        if($path !== '') {
            $this->excludedPaths->add($path);
        }
        return $this;
    }

    public function getExcludedPaths(): Set<string>
    {
        return $this->excludedPaths;
    }

    public function setHackUnitFile(string $hackUnitFile): this
    {
        $this->hackUnitFile = $hackUnitFile;
        return $this;
    }

    public function getHackUnitFile(): ?string
    {
        if ($this->hackUnitFile !== '') {
            return $this->hackUnitFile;
        }
        $cwd = getcwd();
        if( ! is_string($cwd)) {
            return null;
        }
        $path = realpath(getcwd() . '/Hackunit.php');
        if( ! is_string($path)) {
            return null;
        }
        return $path;
    }

    public static function fromCli(Clio $clio): this
    {
        // Use clio to get the settings from the cli
        $toPath = (string $in) : string ==> {
            $path = realpath($in);
            if(is_string($path)) {
                return $path;
            }
            return '';
        };

        $hackunitfile = $clio->option('hackunit-file')->aka('h')
            ->withRequiredValue()
            ->transformedBy($toPath)
            ->describedAs('Boot strap file to include before running the test suite.');

        $excludes = $clio->option('exclude')->aka('e')
            ->withRequiredValue()
            ->transformedBy($toPath)
            ->describedAs('File or folder to exclude.');

        $include = $clio->arg('path-to-tests')
            ->describedAs(
                'Base path for all tests.  The path will be recursively searched for test cases.' . PHP_EOL .
                'If path is a file, only that file will be searched for test cases.' . PHP_EOL .
                'Multiple files/paths may be specified.'
        );

        // Inject the settings from the cli
        $options = new static();

        foreach($clio->allArguments() as $arg) {
            $path = realpath($arg);
            if( ! is_string($path)) {
                $clio->showHelp('Could not find path ' . $path);
                exit();
            }
            $options->addIncludePath($arg);
        }

        foreach($excludes->allValues() as $path) {
            $options->addExcludedPath($path);
        }

        return $options;
    }
}
