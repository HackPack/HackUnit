<?hh // strict

namespace HackPack\HackUnit;

use HackPack\HackUnit\Core\TestResult;
use HackPack\HackUnit\Runner\Runner;
use HackPack\HackUnit\Runner\Options;
use HackPack\HackUnit\Runner\Loading\StandardLoader;
use HackPack\HackUnit\UI\TextReporter;
use kilahm\Clio\Clio;

function run() : void
{
    $clio = Clio::fromCli();
    $options = Options::fromCli($clio);
    $loader = StandardLoader::create($options);
    $reporter = new TextReporter($clio);
    $runner = new Runner($reporter, $options, $loader, new TestResult());
    $runner->run();
}
