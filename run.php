<?hh //partial
require_once 'WasRun.php';
require_once 'TestCaseTest.php';
function main(): void {
    $test2 = new TestCaseTest('testTemplateMethod');
    $test2->run();
    $test3 = new TestCaseTest('testResult');
    $test3->run();
    $test4 = new TestCaseTest('testFailedResult');
    $test4->run();
    $test5 = new TestCaseTest('testFailedResultFormatting');
    $test5->run();
}
main();
