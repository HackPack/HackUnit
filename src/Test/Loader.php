<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Test\Suite;
use SplFileInfo;
use FilesystemIterator;

final class Loader implements \HackPack\HackUnit\Contract\Test\Loader {
  private int $testCount = 0;

  public function __construct(
    private (function(string): Traversable<Suite>) $suiteBuilder,
    private Set<string> $includes = Set {},
    private Set<string> $excludes = Set {},
  ) {}

  public function including(string $path): this {
    $this->includes->add($path);
    return $this;
  }

  public function excluding(string $path): this {
    $fullPath = realpath($path);
    if (is_string($fullPath)) {
      $this->excludes->add($fullPath);
    }
    return $this;
  }

  public function testSuites(): Vector<Suite> {
    $suites = Vector {};
    $builder = $this->suiteBuilder;

    foreach ($this->pathsToScan() as $path) {
      $suite = $builder($path);
      if ($suite !== null) {
        $suites->addAll($suite);
      }
    }

    return $suites;
  }

  private function pathsToScan(): \Generator<int, string, void> {
    foreach ($this->includes as $includeBase) {
      $info = new SplFileInfo($includeBase);
      $rp = $info->getRealPath();
      if (!is_string($rp) || !$info->isReadable()) {
        echo 'skipping '.$rp.PHP_EOL;
        continue;
      }

      if ($info->isFile() && $this->isPathIncluded($rp)) {
        yield $rp;
      }

      if ($info->isDir()) {
        $rdi = new \RecursiveDirectoryIterator(
          $rp,
          FilesystemIterator::CURRENT_AS_FILEINFO |
          FilesystemIterator::UNIX_PATHS |
          FilesystemIterator::SKIP_DOTS,
        );
        /* HH_FIXME[4105] */
        $rfi = new \RecursiveCallbackFilterIterator(
          $rdi,
          $pathInfo ==> {
            $realPath = $pathInfo->getRealPath();
            return
              $pathInfo->isReadable() &&
              is_string($realPath) &&
              $this->isPathIncluded($realPath);
          },
        );
        $rii = new \RecursiveIteratorIterator($rfi);
        foreach ($rii as $fileInfo) {
          $realPath = $fileInfo->getRealPath();
          if (is_string($realPath)) {
            yield $realPath;
          }
        }
      }
    }
  }

  private function isPathIncluded(string $path): bool {
    foreach ($this->excludes as $exclude) {
      if ($path === $exclude || strpos($path, $exclude.'/') === 0) {
        return false;
      }
    }
    return true;
  }
}
