<?hh // strict

namespace HackPack\HackUnit\Util;

final class Options {
  const string VERSION = '0.7';

  public function __construct(
    public \ConstSet<string> $includes,
    public \ConstSet<string> $excludes,
  ) {}

  public static function fromCli(Traversable<string> $args): Options {
    $includes = [];
    $excludes = [];

    $arglist = new Vector($args);
    $arglist->reverse();

    // first arg is always path to executable
    $arglist->pop();

    $addPathToArray = ($path, $array) ==> {
      $realpath = realpath($path);
      if (is_string($realpath)) {
        $array[] = $realpath;
      }
      return $array;
    };

    while ($arglist) {
      $arg = $arglist->pop();
      if (substr($arg, 0, 2) === '--') {
        $path = self::handleLongOption(substr($arg, 2), $arglist);
        if ($path !== '') {
          $excludes = $addPathToArray($path, $excludes);
        }
        continue;
      }
      if (substr($arg, 0, 1) === '-') {
        $path = self::handleShortOption(substr($arg, 1), $arglist);
        if ($path !== '') {
          $excludes = $addPathToArray($path, $excludes);
        }
        continue;
      }
      $includes = $addPathToArray($arg, $includes);
    }

    return new Options(new ImmSet($includes), new ImmSet($excludes));
  }

  private static function handleLongOption(
    string $arg,
    Vector<string> $args,
  ): string {
    $parts = new Vector(explode('=', $arg, 2));
    if ($parts->at(0) !== 'exclude') {
      return '';
    }
    $value = $parts->get(1);
    if ($value === null) {
      $value = self::tryNext($args);
    }
    return $value;
  }

  private static function handleShortOption(
    string $arg,
    Vector<string> $args,
  ): string {
    if (substr($arg, 0, 1) !== 'e') {
      return '';
    }
    $value = substr($arg, 1);
    if ($value === false) {
      $value = self::tryNext($args);
    }
    return $value;
  }
  private static function tryNext(Vector<string> $args): string {
    if ($args->isEmpty() || substr($args->at(0), 0, 1) === '-') {
      return '';
    }
    return $args->pop();
  }
}
