<?hh // strict

namespace HackPack\HackUnit\Coverage;

use HackPack\HackUnit\FileOutlineType;

newtype FileOutlineItem = shape(
    'name' => string,
    'type' => FileOutlineType,
    'line' => int,
);

newtype Token = shape(
    'code' => int,
    'line' => int,
    'raw' => string,
);

class Parser implements \HackPack\HackUnit\Contract\Coverage\Parser
{
    private Map<string, Set<int>> $sourceList = Map{};
    private Map<string, Vector<Token>> $tokenCache = Map{};

    public function __construct(
        private (function(string):string) $fileOutliner,
        private Loader $loader,
    )
    {
    }

    public function fileNames() : Set<string>
    {
        $this->process();
        return $this->sourceList->keys()->toSet();
    }

    public function executableLinesFor(string $file) : Set<int>
    {
        $this->process();
        $lines = $this->sourceList->get($file);
        if($lines === null) {
            return Set{};
        }
        return $lines;
    }

    <<__Memoize>>
    private function process() : void
    {
        $outliner = $this->fileOutliner;

        $this->sourceList = Map::fromItems(
            $this->loader->fileNames()->toVector()
            ->map($fn ==> Pair{
                $fn,
                $this->executableLinesFromOutline($this->parseFileOutline($outliner($fn))),
            })
        );
    }

    private function parseFileOutline(string $fromCli) : Vector<FileOutlineItem>
    {
        $raw = json_decode($fromCli, true);
        return (new Vector($raw))->map($entry ==> {
            $entry = new Map($entry);
            $name = $entry->get('name');
            $type = FileOutlineType::coerce($entry->get('type'));
            $line = $entry->get('line');

            return shape(
                'name' => $name === null ? '' : (string)$name,
                'type' => $type === null ? FileOutlineType::other : $type,
                'line' => $line === null ? -1 : (int)$line,
            );
        });
    }

    private function executableLinesFromOutline(Vector<FileOutlineItem> $outline) : Set<int>
    {
        $lines = Set{};
        foreach($outline as $item) {
            switch($item['type'])
            {
            case FileOutlineType::clss:
                $lines->addAll($this->linesForClass($item['name']));
                break;
            case FileOutlineType::func:
                $lines->addAll($this->linesForFunction($item['name']));
                break;
            case FileOutlineType::other:
                // Nothing to analyze
                break;
            }
        }
        return $lines;
    }

    private function linesForClass(string $className) : Set<int>
    {
        try{
            $cMirror = new \ReflectionClass($className);
        } catch(\ReflectionException $e) {
            return Set{};
        }

        // Interfaces never have executable code
        if($cMirror->isInterface()) {
            return Set{};
        }

        // Attribute to skip an entire class
        if($cMirror->getAttribute('IgnoreCoverage') !== null) {
             return Set{};
        }

        $lines = Set{};
        foreach($cMirror->getMethods() as $method) {

            // Attribute to skip a method
            if($method->getAttribute('IgnoreCoverage') !== null) {
                 continue;
            }

            $start = $method->getStartLine();
            $end = $method->getEndLine();
            $file = $method->getFileName();
            if(
                ! is_int($start) ||
                ! is_int($end) ||
                ! is_string($file)
            ) {
                continue;
            }
            $lines->addAll($this->linesForFile($start + 1, $end, $file));
        }
        return $lines;
    }

    private function linesForFunction(string $functionName) : Set<int>
    {
        try {
            $func = new \ReflectionFunction($functionName);
        } catch(\ReflectionException $e) {
            return Set{};
        }
        $start = $func->getStartLine();
        $end = $func->getEndLine();
        $file = $func->getFileName();
        if(
            ! is_int($start) ||
            ! is_int($end) ||
            ! is_string($file)
        ) {
            return Set{};
        }
        return $this->linesForFile($start + 1, $end, $file);
    }

    private function linesForFile(int $start, int $end, string $filename) : Set<int>
    {
        $tokens = $this->tokenCache->get($filename);
        if($tokens === null) {
            $tokens = $this->tokenize($filename);
            $this->tokenCache->set($filename, $tokens);
        }
        $lines = Set{};
        $skipped = Set{};
        $desiredTokens = $tokens->filter($t ==> $t['line'] >= $start && $t['line'] <= $end);
        foreach($desiredTokens as $token) {
            if(
                $lines->contains($token['line']) ||
                $skipped->contains($token['line'])
            ) {
                continue;
            }
            if($this->isInlineSkip($token)) {
                $skipped->add($token['line'] + 1);
                continue;
            }
            if($this->isExecutable($token)) {
                $lines->add($token['line']);
            }
        }
        return $lines->removeAll($skipped);
    }

    private function tokenize(string $filename) : Vector<Token>
    {
         $tokens = Vector{};
         foreach(token_get_all(file_get_contents($filename)) as $t) {
             if( ! is_array($t) ) {
                 // non-named tokens are never executable
                 continue;
             }
             if(is_int($t[0]) && is_string($t[1]) && is_int($t[2])) {
                 $tokens->add(shape(
                     'code' => $t[0],
                     'raw' => $t[1],
                     'line' => $t[2],
                 ));
             }
         }
         return $tokens;
    }

    private function isExecutable(Token $token) : bool
    {
        switch($token['code'])
        {
        case T_COMMENT:
        case T_DOC_COMMENT:
        case T_WHITESPACE:
        case T_VARIABLE:
            return false;
        default:
            return true;
        }
    }

    private function isInlineSkip(Token $token) : bool
    {
        return
            ($token['code'] === T_COMMENT || $token['code'] === T_DOC_COMMENT) &&
            strpos($token['raw'], 'IgnoreCoverage') !== false;
    }
}
