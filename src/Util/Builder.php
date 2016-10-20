<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Assert;
use HackPack\HackUnit\HackUnit;
use HackPack\HackUnit\Report\Format;
use HackPack\HackUnit\Report\Format\Cli;
use HackPack\HackUnit\Report\Status;
use HackPack\HackUnit\Test\Loader;
use HackPack\HackUnit\Test\Parser;
use HackPack\HackUnit\Test\Runner;
use HackPack\HackUnit\Test\SuiteBuilder;

final class Builder {
  public function fromCli(Traversable<string> $args): this {
    return new self(Options::fromCli($args));
  }

  public function __construct(private Options $options) {}

  public function build(): HackUnit {
    $suiteBuilder = new SuiteBuilder(
      ($className, $fileName) ==> new Parser($className, $fileName),
    );

    $loader = new Loader(
      $class ==> $suiteBuilder->buildSuites($class),
      $this->options->includes->toSet(),
      $this->options->excludes->toSet(),
    );

    $runner = new Runner(class_meth(Assert::class, 'build'));

    return new HackUnit(
      $this->buildReportFormatters(),
      $this->buildStatus(),
      $suiteBuilder,
      $loader,
      $runner,
    );
  }

  private function buildReportFormatters(): Vector<Format> {
    return Vector {new Cli(STDOUT)};
  }

  private function buildStatus(): Status {
    return new Status(STDOUT);
  }
}
