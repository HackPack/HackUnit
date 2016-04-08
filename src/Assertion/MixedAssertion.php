<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureEmitter;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SuccessEmitter;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class MixedAssertion
  implements \HackPack\HackUnit\Contract\Assertion\MixedAssertion {
  use FailureEmitter;
  use SuccessEmitter;

  private bool $negate = false;

  public function __construct(
    private mixed $context,
    Vector<FailureListener> $failureListeners,
    Vector<SuccessListener> $successListeners,
  ) {
    $this->setFailureListeners($failureListeners);
    $this->setSuccessListeners($successListeners);
  }

  public function not(): this {
    $this->negate = true;
    return $this;
  }

  public function isNull(): void {
    if ($this->context === null) {
      $this->negate
        ? $this->fail('Expected context to be non-null.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be null.');
  }

  public function isBool(): void {
    if (is_bool($this->context)) {
      $this->negate
        ? $this->fail('Expected context to not be a bool.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be a bool.');
  }

  public function isInt(): void {
    if (is_int($this->context)) {
      $this->negate
        ? $this->fail('Expected context to not be an integer.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be an integer.');
  }

  public function isFloat(): void {
    if (is_float($this->context)) {
      $this->negate
        ? $this->fail('Expected context to not be an float.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be an float.');
  }

  public function isString(): void {
    if (is_string($this->context)) {
      $this->negate
        ? $this->fail('Expected context to not be an string.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be an string.');
  }

  public function isArray(): void {
    if (is_array($this->context)) {
      $this->negate
        ? $this->fail('Expected context to not be an array.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be an array.');
  }

  public function looselyEquals(mixed $expected): void {
    if ($this->context == $expected) {
      $this->negate
        ? $this->fail('Items expected to not be equal.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Items expected to be equal.');
  }

  public function identicalTo(mixed $expected): void {
    if ($this->context === $expected) {
      $this->negate
        ? $this->fail('Items expected to not be identical.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Items expected to be identical.');
  }

  public function isObject(): void {
    if (is_object($this->context)) {
      $this->negate
        ? $this->fail('Expected context to not be an object.')
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be an object.');
  }

  public function isTypeOf(string $className): void {
    if (is_a($this->context, $className)) {
      $this->negate
        ? $this->fail(
          'Expected context to not be an instance of '.$className.'.',
        )
        : $this->emitSuccess();
      return;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail('Expected context to be an instance of '.$className.'.');
  }

  private function fail(string $message): void {
    $this->emitFailure(Failure::fromCallStack($message));
  }
}
