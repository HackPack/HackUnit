<?hh // strict

namespace HackPack\HackUnit\Event;
use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

final class Success {
  public static function fromCallStack(): this {
    return new static(Trace::findAssertionCall(), Trace::findTestMethod());
  }

  public function __construct(
    private TraceItem $assertionCallSite,
    private TraceItem $testCallSite,
  ) {}

  public function assertionCallSite(): TraceItem {
    return $this->assertionCallSite;
  }

  public function testMethodTraceItem(): TraceItem {
    return $this->testCallSite;
  }

}
