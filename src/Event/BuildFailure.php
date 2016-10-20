<?hh // strict

namespace HackPack\HackUnit\Event;

final class BuildFailure {
  public function __construct(
    private string $path,
    private \Exception $exception,
  ) {}

  public function filePath(): string {
    return $this->path;
  }

  public function exception(): \Exception {
    return $this->exception;
  }
}
