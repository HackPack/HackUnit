<?hh //partial
require_once 'WasRun.php';
require_once 'TestCaseTest.php';
function main(): void {
    $test = new TestCaseTest('testRunning');
    $test->run();
    $test2 = new TestCaseTest('testSetUp');
    $test2->run();
}
main();
