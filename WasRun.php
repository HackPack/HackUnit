<?hh //strict
require_once 'TestCase.php';
class WasRun extends TestCase
{
    public bool $wasRun = false;

    public function testMethod(): void
    {
        $this->wasRun = true;
    }
}
