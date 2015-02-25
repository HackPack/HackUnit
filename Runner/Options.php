<?hh //strict
namespace HackPack\HackUnit\Runner;

<<__ConsistentConstruct>> class Options
{
    protected ?string $testPath;

    protected Set<string> $excludedPaths = Set{};

    protected ?string $hackUnitFile;

    protected static array<string> $longOpts = array(
        'exclude:',
        'hackunit-file:'
    );

    /**
     * @todo Annotate type as "this" when fixed in
     * nightly. Currently broken when using namespaces
     */
    public function setTestPath(string $testPath): Options
    {
        $this->testPath = $testPath;
        return $this;
    }

    public function getIncludedPaths(): Set<string>
    {
        return is_null($this->testPath) ? Set{getcwd()} : Set{$this->testPath};
    }

    public function addExcludedPath(string $path): Options
    {
        $this->excludedPaths->add($path);
        return $this;
    }

    public function getExcludedPaths(): Set<string>
    {
        return $this->excludedPaths;
    }

    public function setHackUnitFile(string $hackUnitFile): Options
    {
        $this->hackUnitFile = $hackUnitFile;
        return $this;
    }

    public function getHackUnitFile(): ?string
    {
        $path = (string) getcwd() . '/Hackunit.php';
        if (! is_null($this->hackUnitFile)) {
            $path = $this->hackUnitFile;
        }
        $path = realpath($path);
        return $path ?: null;
    }

    public static function fromCli(Vector<string> $argv): Options
    {
        $cli = getopt('', static::$longOpts);
        $options = new static();

        if (array_key_exists('exclude', $cli)) {
            $options->excludedPaths->add($cli['exclude']);
        }

        if (array_key_exists('hackunit-file', $cli)) {
            $options->hackUnitFile = $cli['hackunit-file'];
        }

        if($argv) {
            $options->testPath = $argv->pop();
        }
        return $options;
    }
}
