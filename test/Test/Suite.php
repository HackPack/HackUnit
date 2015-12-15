<?hh // strict

namespace HackPack\HackUnit\Tests\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\HackUnit\Test\Suite;
use HackPack\HackUnit\Tests\Mocks\Test\TestCase;
use HackPack\HackUnit\Tests\Fixtures\SpySuite;
use HackPack\HackUnit\Tests\Fixtures\RunCounts;

class SuiteTest
{
    private Suite $suite;
    private Vector<TestCase> $testCases = Vector{};
    private \ReflectionClass $mirror;

    public function __construct()
    {
        $this->mirror = new \ReflectionClass(SpySuite::class);
        $this->suite = $this->buildSuite();
    }

    <<Setup>>
    public function resetCounts() : void
    {
        $this->testCases->clear();
        SpySuite::resetCounts();
        $this->suite = $this->buildSuite();
    }

    public function testCaseBuilder(
        (function(Assert):Awaitable<void>) $test,
        Vector<(function():Awaitable<void>)> $setup,
        Vector<(function():Awaitable<void>)> $teardown,
    ) : TestCase
    {
        $case = new TestCase();
        $case->setup->addAll($setup);
        $case->teardown->addAll($teardown);
        $this->testCases->add($case);
        return $case;
    }

    private function buildSuite() : Suite
    {
        return new Suite($this->mirror, inst_meth($this, 'testCaseBuilder'));
    }

    private function buildSuiteSetup() : \ReflectionMethod
    {
        return $this->mirror->getMethod('suiteUp');
    }

    private function buildSuiteTeardown() : \ReflectionMethod
    {
        return $this->mirror->getMethod('suiteDown');
    }

    private function buildTestSetup() : \ReflectionMethod
    {
        return $this->mirror->getMethod('setup');
    }

    private function buildTestTeardown() : \ReflectionMethod
    {
        return $this->mirror->getMethod('teardown');
    }

    private function buildTestMethod() : \ReflectionMethod
    {
        return $this->mirror->getMethod('test');
    }

    <<Test>>
    public function suiteGivesBuiltTestCases(Assert $assert) : void
    {
        $this->suite->registerTestMethod($this->buildTestMethod());
        $this->suite->registerTestMethod($this->buildTestMethod());
        $cases = $this->suite->testCases();

        // Ensure 2 cases were built
        $assert->int($this->testCases->count())->eq(2);
        // Ensure 2 cases were returned
        $assert->int($cases->count())->eq(2);

        // Ensure the cases returned are the ones built
        foreach($this->testCases as $idx => $case) {
            $assert->mixed($cases->at($idx))
                ->identicalTo($case);
        }
    }

    <<Test>>
    public function testCasesAreNotRun(Assert $assert) : void
    {
        $this->suite->registerTestMethod($this->buildTestMethod());
        $this->suite->testCases()->at(0);

        $cases = $this->suite->testCases();

        $expectedCounts = shape(
            'test up' => 0,
            'test down' => 0,
            'suite up' => 0,
            'suite down' => 0,
            'test' => 0,
        );
        $assert->mixed($this->testCases->at(0)->assert)->isNull();
        TestRunCounter::verifyRunCounts(SpySuite::$counts, $expectedCounts, $assert);
    }

    <<Test>>
    public function setupMethodsArePassedToTestCase(Assert $assert) : void
    {
        // Test added before setup
        $this->suite->registerTestMethod($this->buildTestMethod());
        $this->suite->registerTestSetup($this->buildTestSetup());
        // Test added after setup
        $this->suite->registerTestMethod($this->buildTestMethod());
        // Setup added after second test
        $this->suite->registerTestSetup($this->buildTestSetup());

        // Generate the test cases
        $this->suite->testCases();

        // Each test case should get both setup methods
        $case1 = $this->testCases->at(0);
        $case2 = $this->testCases->at(1);

        $assert->int($case1->setup->count())->eq(2);
        $assert->int($case2->setup->count())->eq(2);

        // The setup methods should be identical
        foreach($case1->setup as $idx => $setup) {
            $assert->mixed($setup)
                ->identicalTo($case2->setup->at($idx));
        }

        // Make sure the passed in reflection methods are called
        $setup1 = $case1->setup->at(0);
        $setup2 = $case1->setup->at(1);

        \HH\Asio\join($setup1());
        $expectedCounts = shape(
            'test up' => 1,
            'test down' => 0,
            'suite up' => 0,
            'suite down' => 0,
            'test' => 0,
        );
        TestRunCounter::verifyRunCounts(
            SpySuite::$counts,
            $expectedCounts,
            $assert,
        );

        \HH\Asio\join($setup2());
        $expectedCounts['test up'] = 2;
        TestRunCounter::verifyRunCounts(
            SpySuite::$counts,
            $expectedCounts,
            $assert,
        );
    }

    <<Test>>
    public function teardownMethodsArePassedToTestCase(Assert $assert) : void
    {
        // Test added before teardown
        $this->suite->registerTestMethod($this->buildTestMethod());
        $this->suite->registerTestTeardown($this->buildTestTeardown());
        // Test added after teardown
        $this->suite->registerTestMethod($this->buildTestMethod());
        // teardown added after second test
        $this->suite->registerTestTeardown($this->buildTestTeardown());

        // Generate the test cases
        $this->suite->testCases();

        // Each test case should get both teardown methods
        $case1 = $this->testCases->at(0);
        $case2 = $this->testCases->at(1);

        $assert->int($case1->teardown->count())->eq(2);
        $assert->int($case2->teardown->count())->eq(2);

        // The teardown methods should be identical
        foreach($case1->teardown as $idx => $teardown) {
            $assert->mixed($teardown)
                ->identicalTo($case2->teardown->at($idx));
        }

        // Make sure the passed in reflection methods are called
        $teardown1 = $case1->teardown->at(0);
        $teardown2 = $case1->teardown->at(1);

        \HH\Asio\join($teardown1());
        $expectedCounts = shape(
            'test up' => 0,
            'test down' => 1,
            'suite up' => 0,
            'suite down' => 0,
            'test' => 0,
        );
        TestRunCounter::verifyRunCounts(
            SpySuite::$counts,
            $expectedCounts,
            $assert,
        );

        \HH\Asio\join($teardown2());
        $expectedCounts['test down'] = 2;
        TestRunCounter::verifyRunCounts(
            SpySuite::$counts,
            $expectedCounts,
            $assert,
        );
    }

    <<Test>>
    public function suiteSetupMethodsAreRegistered(Assert $assert) : void
    {
        $this->suite->registerSuiteSetup($this->buildSuiteSetup());
        $cases = $this->suite->testCases();
        $assert->int($cases->count())->eq(0);

        \HH\Asio\join($this->suite->setup());
        $expectedCounts = shape(
            'test up' => 0,
            'test down' => 0,
            'suite up' => 1,
            'suite down' => 0,
            'test' => 0,
        );
        TestRunCounter::verifyRunCounts(
            SpySuite::$counts,
            $expectedCounts,
            $assert,
        );
    }

    <<Test>>
    public function suiteTeardownMethodsAreRegistered(Assert $assert) : void
    {
        $this->suite->registerSuiteTeardown($this->buildSuiteTeardown());
        $cases = $this->suite->testCases();
        $assert->int($cases->count())->eq(0);

        \HH\Asio\join($this->suite->teardown());
        $expectedCounts = shape(
            'test up' => 0,
            'test down' => 0,
            'suite up' => 0,
            'suite down' => 1,
            'test' => 0,
        );
        TestRunCounter::verifyRunCounts(
            SpySuite::$counts,
            $expectedCounts,
            $assert,
        );
    }
}
