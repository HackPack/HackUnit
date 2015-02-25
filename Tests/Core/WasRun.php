<?hh //strict
namespace HackPack\HackUnit\Tests\Core;

use HackPack\HackUnit\Core\TestCase;

class WasRun extends TestCase
{
    public Vector<string> $log = Vector{};

     public function load(): void
    {
        $this->log->add('load');
    }

     public function unload(): void
    {
        $this->log->add('unload');
    }

     public function setUp(): void
    {
        $this->log->add('setUp');
    }

     public function tearDown(): void
    {
        $this->log->add('tearDown');
    }

    public function testMethod(): void
    {
        $this->log->add('testMethod');
    }

    public function testBrokenMethod(): void
    {
        $this->log->add('brokenMethod');
        throw new \Exception("broken");
    }

    public function testSkip(): void
    {
        $this->markAsSkipped("Skippy");
    }
}
