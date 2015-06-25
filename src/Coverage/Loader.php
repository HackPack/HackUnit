<?hh // strict

namespace HackPack\HackUnit\Coverage;

class Loader implements \HackPack\HackUnit\Contract\Coverage\Loader
{
    private Set<string> $fileNames = Set{};

    public function __construct(
        private Set<string> $baseDirs,
    )
    {
    }

    public function fileNames() : Set<string>
    {
        if($this->baseDirs->isEmpty()) {
             return Set{};
        }

        $exampleDir = $this->baseDirs->values()->at(0);
        $rawFileList = shell_exec('hh_client --retry-if-init true --list-modes ' . escapeshellarg($exampleDir));
        if(! is_string($rawFileList)) {
            throw new \RuntimeException('Unable to scan project directory with hh_client.');
        }

        return (new Set(explode(PHP_EOL, $rawFileList)))
            ->filter($line ==>
                // Never cover hhi files
                substr($line, -4) !== '.hhi' &&
                // Trailing newline causes an empty line after explode
                $line !== '' &&
                // Ensure file is in one of the specified folders
                $this->pathHasBase($line)
            )
            // git rid of the hack mode portion of the line
            ->map($line ==> preg_replace('/^(?:php|decl|partial|strict)\s*(.*)/', '$1', $line))
            ;
    }

    private function pathHasBase(string $path) : bool
    {
        foreach($this->baseDirs as $dir) {
            if(strpos($path, $dir) !== false)
            {
                return true;
            }
        }
        return false;
    }
}
