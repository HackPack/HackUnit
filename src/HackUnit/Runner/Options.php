<?hh //strict
namespace HackUnit\Runner;

class Options
{
    protected ?string $testPath;

    protected ?string $excludedPaths;

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

    public function getTestPath(): string
    {
        return is_null($this->testPath) ? getcwd() : $this->testPath;
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

    public function setHackUnitFile(string $hackUnitFile): Options
    {
        $this->hackUnitFile = $hackUnitFile;
        return $this;
    }

    public function getHackUnitFile(): ?string
    {
        $path = realpath($this->hackUnitFile);
        return $path ?: null;
    }

    public static function fromCli(array<string> $argv): Options
    {
        $cli = getopt('', static::$longOpts);
        $options = new static();

        if (array_key_exists('exclude', $cli)) {
            $options->setExcludedPaths($cli['exclude']);
        }

        if (array_key_exists('hackunit-file', $cli)) {
            $options->setHackUnitFile($cli['hackunit-file']);
        }

        $testPath = $argv[count($argv) - 1];

        /**
         * TODO check based on diff between getopt and argv instead of file existence
         */
        $isValidPath = file_exists($testPath) && realpath($testPath) != $options->getHackUnitFile(); 
        if ($isValidPath) {
            $options->setTestPath($argv[count($argv) - 1]);
        }

        return $options;
    }
}
