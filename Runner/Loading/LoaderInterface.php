<?hh //strict
namespace HackPack\HackUnit\Runner\Loading;

use HackPack\HackUnit\Core\TestSuite;
use HackPack\HackUnit\Core\TestCase;

interface LoaderInterface
{
    /**
     * Load all TestCase objects into a single TestSuite
     * object
     */
    public function loadSuite(): TestSuite;
}
