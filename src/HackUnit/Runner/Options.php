<?hh //strict
namespace HackUnit\Runner;

class Options
{
    protected ?string $testPath;

    protected ?string $excludedPaths;

    protected static array<string> $longOpts = array(
        'exclude'
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

    public function getTestPath(): ?string
    {
        return $this->testPath;
    }

    public function setExcludedPaths(string $paths): Options
    {
        $this->excludedPaths = $paths;
        return $this;
    }

    public function getExcludedPaths(): Set<string>
    {
        $paths = preg_split('/\s+/', $this->excludedPaths);
        return new Set($paths);
    }

    public static function fromCli(array<string> $argv): Options
    {
        $cli = getopt('', static::$longOpts);
        $options = new static();

        if (array_key_exists('exclude', $cli)) {
            $options->setExcludedPaths($cli['exclude']);
        }

        return $options->setTestPath($argv[count($argv) - 1]);
    }
}
