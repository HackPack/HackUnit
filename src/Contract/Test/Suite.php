<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\TestStartListener;

interface Suite {
  public function up(): Awaitable<void>;
  public function run(
    Assert $assert,
    (function(): void) $testPassed,
    \ConstVector<TestStartListener> $testStartListeners,
  ): Awaitable<void>;
  public function down(): Awaitable<void>;
  public function name(): string;
}
