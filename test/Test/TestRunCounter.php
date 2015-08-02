<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Tests\Fixtures\RunCounts;

class TestRunCounter
{
    public static function verifyRunCounts(RunCounts $actual, RunCounts $expected, Assert $assert) : void
    {
        $assert->int($actual['suite up'])->eq($expected['suite up']);
        $assert->int($actual['suite down'])->eq($expected['suite down']);
        $assert->int($actual['test up'])->eq($expected['test up']);
        $assert->int($actual['test down'])->eq($expected['test down']);
        $assert->int($actual['test'])->eq($expected['test']);
    }
}
