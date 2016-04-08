<?hh // strict

namespace HackPack\HackUnit\Tests\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\Success;
use HackPack\HackUnit\Event\SuccessListener;

trait AssertionTest {
  private int $successCount = 0;
  private Vector<Failure> $failEvents = Vector {};

  private function successListeners(): Vector<SuccessListener> {
    return Vector {
      () ==> {
        $this->successCount++;
      },
    };
  }

  private function failListeners(): Vector<FailureListener> {
    return Vector {
      ($e) ==> {
        $this->failEvents->add($e);
      },
    };
  }
}
