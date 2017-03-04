<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Event\TestEndListener;
use HackPack\HackUnit\Event\TestStartListener;
use HackPack\HackUnit\Util\TraceItem;

class SkippedSuite implements \HackPack\HackUnit\Contract\Test\Suite {
  public function __construct(private string $name, private TraceItem $trace) {}

  public async function up(): Awaitable<void> {}

  public async function down(): Awaitable<void> {}

  public async function run(
    Assert $assert,
    (function(): void) $testPassed,
    \ConstVector<TestStartListener> $testStartListeners,
    \ConstVector<TestEndListener> $testEndListeners,
  ): Awaitable<void> {
    $assert->skip('Class '.$this->name.' marked "Skipped"', $this->trace);
  }

  public function name(): string {
    return $this->name;
  }
}
