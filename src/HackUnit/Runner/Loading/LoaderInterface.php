<?hh //strict
namespace HackUnit\Runner\Loading;

use HackUnit\Core\TestSuite;
use HackUnit\Core\TestCase;

interface LoaderInterface
{
    public function loadSuite(): TestSuite;

    public function load(): Vector<TestCase>;

    public function getTestCasePaths(): Set<string>;
}
