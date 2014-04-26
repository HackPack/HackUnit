<?hh //strict
namespace HackUnit\UI;

use HackUnit\Core\TestResult;

class Text
{
    public function __construct(protected TestResult $result)
    {
    }

    public function getFooter(): string
    {
        return $this->result->getSummary();
    }
}
