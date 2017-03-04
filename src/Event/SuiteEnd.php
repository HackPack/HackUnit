<?hh // strict

namespace HackPack\HackUnit\Event;

class SuiteEnd {
  public function __construct(private string $suiteName) {}

  public function suiteName(): string {
    return $this->suiteName;
  }
}
