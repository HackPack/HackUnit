<?hh // strict

namespace HackPack\HackUnit\Event;

use HackPack\HackUnit\Util\Trace;
use HackPack\HackUnit\Util\TraceItem;

final class MalformedSuite {
  public function __construct(
    private TraceItem $item,
    private string $reason,
  ) {}

  public function message(): string {
    return $this->reason;
  }

  public function traceItem(): TraceItem {
    return $this->item;
  }
}
