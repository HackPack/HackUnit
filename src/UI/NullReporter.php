<?hh // strict

namespace HackPack\HackUnit\UI;

use HackPack\HackUnit\Core\TestResult;

final class NullReporter implements ReporterInterface
{
    public static function create(): this
    {
        return new self();
    }

    public function showInfo(): void
    {
    }

    public function showSuccess(...): void
    {
    }

    public function showFailure(...): void
    {
    }

    public function showSkipped(...): void
    {
    }

    public function showReport(TestResult $result): void
    {
    }
}
