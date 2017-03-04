<?hh // strict

namespace HackPack\HackUnit\Event;

class TestStart {
  public function __construct(
    private string $suiteName,
    private string $testName,
  ) {}

  public function suiteName(): string {
    return $this->suiteName;
  }

  public function testName(): string {
    return $this->testName;
  }
}
