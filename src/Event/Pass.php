<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

final class Pass {
  public static function fromCallStack(): this {
    return new static(Trace::findTestMethod());
  }
  public function __construct(private TraceItem $testCallSite) {}

  public function testMethodTraceItem(): TraceItem {
    return $this->testCallSite;
  }
}
