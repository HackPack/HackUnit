<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

<<TestSuite>>
class TeardownParams
{
    <<TearDown>>
    public function takeItDown(string $required) : void
    {
    }
}
