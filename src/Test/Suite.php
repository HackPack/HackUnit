<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\TestCase;
use HackPack\HackUnit\Event\Interruption;
use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;
use HH\Asio;

type Test = shape(
  'factory' => (function(): Awaitable<mixed>),
  'method' => InvokerWithParams,
  'trace item' => TraceItem,
  'skip' => bool,
  'data provider' => (function(): AsyncIterator<array<mixed>>),
);

class Suite implements \HackPack\HackUnit\Contract\Test\Suite {

  public function __construct(
    private \ConstVector<Test> $tests = Vector {},
    private \ConstVector<InvokerWithParams> $suiteup = Vector {},
    private \ConstVector<InvokerWithParams> $suitedown = Vector {},
    private \ConstVector<InvokerWithParams> $testup = Vector {},
    private \ConstVector<InvokerWithParams> $testdown = Vector {},
  ) {}

  public async function run(
    Assert $assert,
    (function(): void) $testPassed,
  ): Awaitable<void> {
    await (async (Test $test) ==> {

             if ($test['skip']) {
               try {
                 $assert->skip('Test marked <<Skip>>', $test['trace item']);
               } catch (Interruption $e) {
                 // any listeners should have been notified by now
               }
               return;
             }

             $instance = await $test['factory']();

             await ($this->testup->map($pretest ==> $pretest($instance, []))
                      |> Asio\v($$));

             $results = Vector {};
             foreach ($test['data provider']() await as $data) {
               array_unshift($data, $assert);
               $results->add($test['method']($instance, $data));
             }
             $results = await Asio\vw($results);
             foreach ($results as $result) {
               if ($result->isSucceeded()) {
                 $testPassed();
               }

               if ($result->isFailed()) {
                 $exception = $result->getException();
                 if (!($exception instanceof Interruption)) {
                   throw $exception;
                 }
               }
             }

             await $this->testdown->map(
               $posttest ==> $posttest($instance, []),
             )
               |> Asio\v($$);
           })
      |> $this->tests->map($$)
      |> Asio\v($$);
  }

  public async function up(): Awaitable<void> {
    await \HH\Asio\v($this->suiteup->map($f ==> $f(null, [])));
  }

  public async function down(): Awaitable<void> {
    await \HH\Asio\v($this->suitedown->map($f ==> $f(null, [])));
  }
}
