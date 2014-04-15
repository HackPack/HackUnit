<?hh //partial
require_once 'WasRun.php';
require_once 'TestCaseTest.php';
function main(): void {
    $test2 = new TestCaseTest('testTemplateMethod');
    $test2->run();
}
main();
