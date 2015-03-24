<?hh //strict
namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestResult;

interface ReporterInterface
{
    public function showInfo(): void;
    public function showSuccess(...): void;
    public function showFailure(...): void;
    public function showSkipped(...): void;
    public function showReport(TestResult $result): void;
}
