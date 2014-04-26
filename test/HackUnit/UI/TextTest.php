<?hh //strict
namespace HackUnit\UI;

use HackUnit\Core\TestCase;
use HackUnit\Core\TestResult;
use HackUnit\Core\ExpectationException;

class TextTest extends TestCase
{
    protected ?Text $ui;

    <<Override>> public function setUp(): void
    {
        $result = $this->getResult();
        $this->ui = new Text($result);
    }

    public function test_getFooter_should_return_count_summary(): void
    {
        if ($this->ui == null) throw new ExpectationException("ui can't be null");
        $this->expect($this->ui->getFooter())->toEqual("1 run, 1 failed");
    }

    protected function getResult(): TestResult
    {
        $result = new TestResult();
        $result->testStarted();
        try {
            throw new \Exception("Something is wrong");
        } catch (\Exception $e) {
            $result->testFailed($e);
        }
        return $result;
    }
}
