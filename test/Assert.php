<?hh // strict

namespace HackPack\HackUnit\Tests;

use HackPack\HackUnit\Contract\Assert as iAssert;
use HackPack\HackUnit\Assert;
use HackPack\HackUnit\Event\Skip;

class AssertTest {
  use TraceItemTest;

  private Vector<Skip> $skips = Vector {};

  <<Setup>>
  public function clearEvents(): void {
    $this->skips->clear();
  }

  <<Test>>
  public function skipIdentifiesCaller(iAssert $assert): void {
    $sut = new Assert(
      Vector {},
      Vector {
        $skip ==> {
          $this->skips->add($skip);
        },
      },
      Vector {},
    );
    $line = __LINE__ + 1;
    $sut->skip('testing');

    $assert->int($this->skips->count())->eq(1);
    $skip = $this->skips->at(0);

    $this->checkTrace(
      $skip->skipCallSite(),
      shape(
        'line' => $line,
        'function' => __FUNCTION__,
        'class' => __CLASS__,
        'file' => __FILE__,
      ),
      $assert,
    );

    $assert->string($skip->message())->is('testing');
  }
}
