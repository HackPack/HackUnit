<?hh //partial
require_once 'src/HackUnit/ClassLoader.php';

use HackUnit\ClassLoader;
use HackUnit\Core\TestResult;
use HackUnit\Loading\ConventionalLoader;
use HackUnit\UI\Text;

function main(): void {
    $loader = new ConventionalLoader(__DIR__ . '/test', Vector {__DIR__ . '/test/fixtures'});
    $suite = $loader->loadSuite();
    $result = $suite->run(new TestResult());
    $text = new Text($result);
    print $text->getReport();
}

ClassLoader::addSearchPath(__DIR__ . '/test');
ClassLoader::register();
main();
