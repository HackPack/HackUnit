<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

final class Skip {
  public static function fromCallStack(string $message): this {
    return
      new static($message, Trace::findTestMethod(), Trace::findTestMethod());
  }

  public function __construct(
    private string $reason,
    private TraceItem $callSite,
    private TraceItem $testSite,
  ) {}

  public function message(): string {
    return $this->reason;
  }

  public function skipCallSite(): TraceItem {
    return $this->callSite;
  }

  public function testMethodTraceItem(): TraceItem {
    return $this->testSite;
  }
}
