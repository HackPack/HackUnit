<?hh //partial

use HackUnit\Core\TestSuite;
use HackUnit\Core\TestCaseTest;
use HackUnit\Core\TestResult;

function autoload($class): void {
    $directories = [__DIR__ . '/test', __DIR__ . '/src'];
    $parts = explode('\\', $class);
    $path = implode('/', $parts);
    foreach ($directories as $dir) {
        $absPath = $dir . '/' . $path . '.php';
        if (file_exists($absPath)) {
            require_once($absPath);
            break;
        }
    }
}

function register(): void {
    spl_autoload_register('autoload');
}

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

register();
main();
