<?hh // strict

namespace HackPack\HackUnit\Contract\Test;

use HackPack\HackUnit\Contract\Assert;

interface Suite {
  public function up(): Awaitable<void>;
  public function run(
    Assert $assert,
    (function(): void) $testPassed,
  ): Awaitable<void>;
  public function down(): Awaitable<void>;
  public function name(): string;
}
