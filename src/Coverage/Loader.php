<?hh // strict

namespace HackPack\HackUnit\Coverage;

class Loader implements \HackPack\HackUnit\Contract\Coverage\Loader
{
    const int HH_SERVER_MAX_RETRIES = 10;
    const int HH_SERVER_COOLDOWN = 300;

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
        $this->startHHServer($exampleDir);
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

    private function startHHServer(string $baseDir) : void
    {
        $retries = 0;
        $return = 0;
        $out = [];
        while($retries < self::HH_SERVER_MAX_RETRIES) {
            exec('hh_client check --from hackunit ' . escapeshellarg($baseDir) . ' 2>&1', $out, $return);
            if($return === 0) {
                return;
            }
            echo 'Trying to start hh_server... ' . PHP_EOL;
            usleep(self::HH_SERVER_COOLDOWN);
        }
        throw new \RuntimeException('Unable to start hh_server');
    }
}
