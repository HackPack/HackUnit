<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\TraceItem;

trait TraceItemTest {
  private function checkTrace(
    TraceItem $actual,
    TraceItem $expected,
    Assert $assert,
  ): void {
    $line = $expected['line'];
    $method = $expected['function'];
    $class = $expected['class'];
    $file = $expected['file'];

    if (is_int($line)) {
      $assert->mixed($actual['line'])->isInt();
      $assert->int((int) $actual['line'])->eq($line);
    } else {
      $assert->mixed($actual['line'])->isNull();
    }

    if (is_string($method)) {
      $assert->mixed($actual['function'])->isString();
      $assert->string((string) $actual['function'])->is($method);
    } else {
      $assert->mixed($actual['function'])->isNull();
    }

    if (is_string($class)) {
      $assert->mixed($actual['class'])->isString();
      $assert->string((string) $actual['class'])->is($class);
    } else {
      $assert->mixed($actual['class'])->isNull();
    }

    if (is_string($file)) {
      $assert->mixed($actual['file'])->isString();
      $assert->string((string) $actual['file'])->is($file);
    } else {
      $assert->mixed($actual['file'])->isNull();
    }
  }
}
