<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures;

use HackPack\HackUnit\Assertion\AssertionBuilder;

/**
 * Loader should have no problems with this suite
 */
<<TestSuite>>
class ValidSuite
{
    <<Setup('suite','test')>>
    public function setupTestAndSuite() : void
    {
    }

    <<Setup('suite')>>
    public function setupSuiteOnly() : void
    {
    }

    <<Setup('test')>>
    public function setupTestOnly() : void
    {
    }

    <<Setup>>
    public function genericSetup() : void
    {
    }

    <<Teardown('suite','test')>>
    public function teardownTestAndSuite() : void
    {
    }

    <<Teardown('suite')>>
    public function teardownSuiteOnly() : void
    {
    }

    <<Teardown('test')>>
    public function teardownTestOnly() : void
    {
    }

    <<Teardown>>
    public function genericTeardown() : void
    {
    }

    <<Setup,teardown>>
    public function setupAndTeardown() : void
    {
    }

    <<Test>>
    public function validTest1(AssertionBuilder $assert) : void
    {
    }

    <<Test>>
    public function validTest2(AssertionBuilder $assert) : void
    {
    }
}
