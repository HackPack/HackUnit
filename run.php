<?hh //partial
require_once 'WasRun.php';
require_once 'TestCaseTest.php';
require_once 'TestSuite.php';
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
main();
