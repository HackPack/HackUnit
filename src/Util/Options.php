<?hh // strict

namespace HackPack\HackUnit\Util;

final class Options
{
    const string VERSION = '0.4-dev';

    public Set<string> $includes = Set{};
    public Set<string> $excludes = Set{};
    public Set<string> $sourceFolders = Set{};
    public bool $colors = true;

    public static function fromCli(\kilahm\Clio\Clio $clio) : this
    {
        $options = new static();

        $excludes = $clio->option('exclude')
            ->aka('ignore')
            ->aka('e')
            ->describedAs('File or directory to exclude when loading test suites.  This option may be specified multiple times.')
            ->withRequiredValue()
            ;

        $color = $clio->flag('no-color')
            ->describedAs('Disable ANSI color codes.')
            ;

        // Includes will be the cli arguments
        $clio->arg('path')
            ->describedAs('File or directory to include when loading test suites.  Multiple files and/or directories may be specified.');

        foreach($clio->allArguments() as $path)
        {
            $fullPath = realpath($path);
            if($fullPath === false) {
                $clio->showHelp('Unable to locate path ' . $path);
            }
        }

        $options->includes->addAll($clio->allArguments());
        $options->excludes->addAll($excludes->allValues());
        if($color->wasPresent()) {
             $options->colors = false;
        }

        return $options;
    }
}
