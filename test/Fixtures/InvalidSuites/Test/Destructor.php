<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\Test;

<<TestSuite>>
class Destructor
{
    <<Test>>
    public function __destruct()
    {
    }
}
