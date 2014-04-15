<?hh //strict
require_once 'TestCase.php';
class WasRun extends TestCase
{
    public bool $wasRun = false;
    public bool $wasSetUp = false;

    public function setUp(): void
    {
        $this->wasSetUp = true;
    }

    public function testMethod(): void
    {
        $this->wasRun = true;
    }
}
