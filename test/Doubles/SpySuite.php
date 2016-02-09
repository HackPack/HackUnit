<?hh // strict

namespace HackPack\HackUnit\Tests\Doubles;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Contract\Test\Suite;

type RunCounts = shape(
    'up' => int,
    'down' => int,
    'run' => int,
);

class SpySuite implements Suite
{
    public RunCounts $counts = shape(
        'up' => 0,
        'down' => 0,
        'run' => 0,
    );

    public Vector<Assert> $asserts = Vector{};


    public async function up() : Awaitable<void>
    {
        $this->counts['up']++;
    }

    public async function down() : Awaitable<void>
    {
        $this->counts['down']++;
    }

    public async function run(Assert $assert) : Awaitable<void>
    {
        $this->asserts->add($assert);
        $this->counts['run']++;
    }

}

