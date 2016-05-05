<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Parser;
use FredEmmott\DefinitionFinder\TreeParser;

abstract class ParserTest {

  abstract protected static function basePath(): string;
  abstract protected static function fullName(string $name): string;

  protected static Map<string, Parser> $parsersBySuiteName = Map {};

  <<Setup('suite')>>
  public static function buildParsers(): void {
    self::$parsersBySuiteName->clear()->addAll(
      TreeParser::FromPath(static::basePath())
        ->getClasses()
        ->map(
          $class ==> Pair {
            $class->getName(),
            new Parser($class->getName(), $class->getFileName()),
          },
        ),
    );
  }

  protected function parserFromSuiteName(string $name): Parser {
    $parser = self::$parsersBySuiteName->get(static::fullName($name));

    if ($parser === null) {
      throw new \RuntimeException('Unable to locate suite '.$name);
    }
    return $parser;
  }
}
