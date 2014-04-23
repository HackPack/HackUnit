<?hh //partial
require_once 'src/HackUnit/Loader.php';

use HackUnit\Loader;
use HackUnit\Core\TestResult;
use HackUnit\Loading\ConventionalLoader;

function main(): void {
    $loader = new ConventionalLoader(__DIR__ . '/test', Vector {__DIR__ . '/test/fixtures'});
    $suite = $loader->loadSuite();
    $result = $suite->run(new TestResult());
    print "\n" . $result->getSummary() . "\n";
}

Loader::add(__DIR__ . '/test');
Loader::register();
main();
