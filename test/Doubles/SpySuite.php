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
    public static RunCounts $counts = shape(
        'up' => 0,
        'down' => 0,
        'run' => 0,
    );

    public static Vector<Assert> $asserts = Vector{};

    public static function resetCounts() : void
    {
        self::$counts = shape(
            'up' => 0,
            'down' => 0,
            'run' => 0,
        );
    }

    public async function up() : Awaitable<void>
    {
        self::$counts['up']++;
    }

    public async function down() : Awaitable<void>
    {
        self::$counts['down']++;
    }

    public async function run(Assert $assert) : Awaitable<void>
    {
        self::$asserts->add($assert);
        self::$counts['run']++;
    }

}

