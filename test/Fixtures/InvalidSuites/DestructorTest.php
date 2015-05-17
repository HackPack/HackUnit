<?hh // strict

namespace HackPack\HackUnit\Tests\Fixtures\InvalidSuites;

<<TestSuite>>
class DestructorTest
{
    <<Test>>
    public function __destruct()
    {
    }
}
