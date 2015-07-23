<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\ValidSuite\IgnoreMe;

use HackPack\HackUnit\Contract\Assert;

/**
 * Loader should have no problems with this suite
 */
<<TestSuite>>
class ValidSuite2
{
    public function __construct(string $notRequired = '')
    {
    }

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

    <<TearDown('suite','test')>>
    public function teardownTestAndSuite() : void
    {
    }

    <<TearDown('suite')>>
    public function teardownSuiteOnly() : void
    {
    }

    <<TearDown('test')>>
    public function teardownTestOnly() : void
    {
    }

    <<TearDown>>
    public function genericTearDown() : void
    {
    }

    <<Setup,TearDown>>
    public function setupAndTearDown() : void
    {
    }

    <<Test>>
    public function validTest1(Assert $assert) : void
    {
    }

    <<Test>>
    public function validTest2(Assert $assert) : void
    {
    }

    public function notATest(Assert $assert) : void
    {
    }
}
