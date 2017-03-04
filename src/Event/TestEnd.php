<?hh // strict

namespace HackPack\HackUnit\Event;

final class TestEnd {
  public function __construct(
    private string $suiteName,
    private string $testName,
    private ?string $file,
    private ?int $line,
  ) {}

  public function suiteName(): string {
    return $this->suiteName;
  }

  public function testName(): string {
    return $this->testName;
  }

  public function file(): string {
    return $this->file === null ? '??' : $this->file;
  }

  public function line(): int {
    return $this->line === null ? -1 : $this->line;
  }
}
