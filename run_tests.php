<?hh //partial
require_once 'src/HackUnit/Loader.php';

use HackUnit\Loader;
use HackUnit\Core\TestSuite;
use HackUnit\Core\TestCaseTest;
use HackUnit\Core\TestResult;

function main(): void {
    $suite = new TestSuite();
    $suite->add(new TestCaseTest('testTemplateMethod'));
    $suite->add(new TestCaseTest('testResult'));
    $suite->add(new TestCaseTest('testFailedResult'));
    $suite->add(new TestCaseTest('testFailedResultFormatting'));
    $suite->add(new TestCaseTest('testSuite'));
    $result = $suite->run(new TestResult());
    print "\n" . $result->getSummary() . "\n";
}

Loader::add(__DIR__ . '/test');
Loader::register();
main();
