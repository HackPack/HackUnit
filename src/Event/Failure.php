<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

<<__ConsistentConstruct>>
class Failure {
  public static function fromCallStack(string $message): this {
    return new static(
      $message,
      Trace::findAssertionCall(),
      Trace::findTestMethod(),
    );
  }

  public function __construct(
    private string $message,
    private TraceItem $assertionCallSite,
    private TraceItem $testCallSite,
  ) {}

  public function assertionTraceItem(): TraceItem {
    return $this->assertionCallSite;
  }

  public function testMethodTraceItem(): TraceItem {
    return $this->testCallSite;
  }

  public function getMessage(): string {
    return $this->message;
  }
}
