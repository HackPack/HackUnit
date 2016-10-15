<?hh // strict

namespace HackPack\HackUnit\Tests\Doubles;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\Suite;

type RunCounts = shape(
  'up' => int,
  'down' => int,
  'run' => int,
);

class SpySuite implements Suite {

  public RunCounts $counts = shape('up' => 0, 'down' => 0, 'run' => 0);
  public Vector<Assert> $asserts = Vector {};
  public Vector<(function(): void)> $passCallbacks = Vector {};

  private (function(): void) $runAction;

  public function __construct(?(function(): void) $runAction = null) {
    $this->runAction =
      $runAction === null
        ? () ==> {
        }
        : $runAction;
  }

  public function name(): string {
    return 'Spy Suite';
  }

  public async function up(): Awaitable<void> {
    $this->counts['up']++;
  }

  public async function down(): Awaitable<void> {
    $this->counts['down']++;
  }

  public async function run(
    Assert $assert,
    (function(): void) $testPassed,
  ): Awaitable<void> {
    $this->asserts->add($assert);
    $this->passCallbacks->add($testPassed);
    $this->counts['run']++;
    $action = $this->runAction;
    $action();
  }

}
