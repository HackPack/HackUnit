<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\TestCase;
use HackPack\HackUnit\Event\Interruption;
use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;
use HH\Asio;

type Test = shape(
    'factory' => (function():Awaitable<mixed>),
    'method' => InvokerWithParams,
    'trace item' => TraceItem,
    'skip' => bool,
);

class Suite implements \HackPack\HackUnit\Contract\Test\Suite
{

    public function __construct(
        private \ConstVector<Test> $tests = Vector{},
        private \ConstVector<InvokerWithParams> $suiteup = Vector{},
        private \ConstVector<InvokerWithParams> $suitedown = Vector{},
        private \ConstVector<InvokerWithParams> $testup = Vector{},
        private \ConstVector<InvokerWithParams> $testdown = Vector{},
    )
    {
    }

    public async function run(Assert $assert) : Awaitable<void>
    {
        await Asio\v($this->tests->map(async ($test) ==> {

            if($test['skip']) {
                $assert->skip('Test marked <<Skip>>', $test['trace item']);
                return;
            }

            $instance = await $test['factory']();
            await Asio\v($this->testup->map($pretest ==> $pretest($instance, [])));

            try{
                await $test['method']($instance, [$assert]);
            } catch (Interruption $e) {
                // any listeners should have been notified by now
            }

            await Asio\v($this->testdown->map($posttest ==> $posttest($instance, [])));
        }));
    }


    public async function up() : Awaitable<void>
    {
        await \HH\Asio\v($this->suiteup->map($f ==> $f(null, [])));
    }

    public async function down() : Awaitable<void>
    {
        await \HH\Asio\v($this->suitedown->map($f ==> $f(null, [])));
    }

    private function buildSkipTest(\ReflectionMethod $m, string $reason) : (function(Assert):Awaitable<void>)
    {
        return async (Assert $assert) ==> {
            $assert->skip(
                $reason,
                Trace::buildItem([
                    'file' => $m->getFileName(),
                    'line' => $m->getStartLine(),
                    'function' => $m->getName(),
                    'class' => $m->getDeclaringClass()->getName(),
                ]),
            );
        };
    }
}
