<?hh //partial
require_once 'src/HackUnit/ClassLoader.php';

use HackUnit\ClassLoader;
use HackUnit\Core\TestResult;
use HackUnit\Loading\ConventionalLoader;

function main(): void {
    $loader = new ConventionalLoader(__DIR__ . '/test', Vector {__DIR__ . '/test/fixtures'});
    $suite = $loader->loadSuite();
    $result = $suite->run(new TestResult());
    print "\n" . $result->getSummary() . "\n";
}

ClassLoader::addSearchPath(__DIR__ . '/test');
ClassLoader::register();
main();
