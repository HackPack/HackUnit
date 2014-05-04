<?hh //partial
$autoloader = include( __DIR__ . '/vendor/autoload.php');
$autoloader->add('HackUnit', __DIR__ . '/test');

use HackUnit\Core\TestResult;
use HackUnit\Runner\Loading\ConventionalLoader;
use HackUnit\UI\Text;

function main(): void {
    $loader = new ConventionalLoader(__DIR__ . '/test', Vector {__DIR__ . '/test/fixtures'});
    $suite = $loader->loadSuite();
    $result = $suite->run(new TestResult());
    $text = new Text($result);
    print $text->getReport();
}

main();
