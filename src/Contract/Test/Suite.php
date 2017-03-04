<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\TestStartListener;
use HackPack\HackUnit\Event\TestEndListener;

interface Suite {
  public function up(): Awaitable<void>;
  public function run(
    Assert $assert,
    (function(): void) $testPassed,
    \ConstVector<TestStartListener> $testStartListeners,
    \ConstVector<TestEndListener> $testEndListeners,
  ): Awaitable<void>;
  public function down(): Awaitable<void>;
  public function name(): string;
}
