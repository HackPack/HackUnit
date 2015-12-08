<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\Test;

class Destructor
{
    <<Test>>
    public function __destruct()
    {
    }
}
