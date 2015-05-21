<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites\Test;

<<TestSuite>>
class DestructorTest
{
    <<Test>>
    public function __destruct()
    {
    }
}
