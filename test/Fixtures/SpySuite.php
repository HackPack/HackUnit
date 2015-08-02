<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures;

use HackPack\HackUnit\Contract\Assert;

type RunCounts = shape(
    'test up' => int,
    'test down' => int,
    'suite up' => int,
    'suite down' => int,
    'test' => int,
);

class SpySuite
{
    public static RunCounts $counts = shape(
        'test up' => 0,
        'test down' => 0,
        'suite up' => 0,
        'suite down' => 0,
        'test' => 0,
    );

    public static Vector<Assert> $asserts = Vector{};

    public static function resetCounts() : void
    {
        self::$counts = shape(
            'test up' => 0,
            'test down' => 0,
            'suite up' => 0,
            'suite down' => 0,
            'test' => 0,
        );
    }

    public function setup() : void
    {
        self::$counts['test up']++;
    }

    public function teardown() : void
    {
        self::$counts['test down']++;
    }

    public function suiteUp() : void
    {
        self::$counts['suite up']++;
    }

    public function suiteDown() : void
    {
        self::$counts['suite down']++;
    }

    public function test(Assert $assert) : void
    {
        self::$asserts->add($assert);
        self::$counts['test']++;
    }

}

