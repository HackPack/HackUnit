<?hh // strict

namespace HackPack\HackUnit\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Util\TraceItem;

class SkippedSuite implements \HackPack\HackUnit\Contract\Test\Suite
{
    public function __construct(
        private string $name,
        private TraceItem $trace,
    )
    {
    }

    public async function up() : Awaitable<void>
    {
    }

    public async function down() : Awaitable<void>
    {
    }

    public async function run(Assert $assert) : Awaitable<void>
    {
        $assert->skip('Class ' . $this->name . ' marked "Skipped"', $this->trace);
    }

}
