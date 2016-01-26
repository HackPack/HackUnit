<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\TestCase;
use HackPack\HackUnit\Util\Trace;
use HH\Asio;
use ReflectionMethod;

type Test = shape(
    'factory' => (function():mixed),
    'method' => ReflectionMethod,
    'skip' => bool,
);

class Suite implements \HackPack\HackUnit\Contract\Test\Suite
{

    public function __construct(
        private \ConstVector<Test> $tests = Vector{},
        private \ConstVector<ReflectionMethod> $suiteup = Vector{},
        private \ConstVector<ReflectionMethod> $suitedown = Vector{},
        private \ConstVector<ReflectionMethod> $testup = Vector{},
        private \ConstVector<ReflectionMethod> $testdown = Vector{},
    )
    {
    }

    public async function run(Assert $assert) : Awaitable<void>
    {
        await Asio\v($this->tests->map(async ($test) ==> {
            $instance = $test['factory']();
            await Asio\v($this->testup->map($pretest ==> $this->awaitOrRun($pretest, $instance)));
            await $this->awaitOrRun($test['method'], $instance, [$assert]);
            await Asio\v($this->testdown->map($pretest ==> $this->awaitOrRun($pretest, $instance)));
        }));
    }


    public async function up() : Awaitable<void>
    {
        await \HH\Asio\v($this->suiteup->map($f ==> $this->awaitOrRun($f, null)));
    }

    public async function down() : Awaitable<void>
    {
        await \HH\Asio\v($this->suitedown->map($f ==> $this->awaitOrRun($f, null)));
    }

    private async function awaitOrRun(ReflectionMethod $m, mixed $instance, array<mixed> $params = []) : Awaitable<void>
    {
        $result = $m->isStatic() ?
            $m->invokeArgs(null, $params) :
            $m->invokeArgs($instance, $params);

        if($m->isAsync()) {
            await $result;
        }
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
