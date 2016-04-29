<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureEmitter;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SuccessEmitter;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class KeyedContainerAssertion<Tkey, Tval>
  implements
    \HackPack\HackUnit\Contract\Assertion\KeyedContainerAssertion<Tkey,
    Tval> {

  use FailureEmitter;
  use SuccessEmitter;

  private bool $negate = false;
  private \ConstMap<Tkey, Tval> $context;

  public function __construct(
    KeyedContainer<Tkey, Tval> $context,
    Vector<FailureListener> $failureListeners,
    Vector<SuccessListener> $successListeners,
  ) {
    $this->context = new Map($context);
    $this->setFailureListeners($failureListeners);
    $this->setSuccessListeners($successListeners);
  }

  public function not(): this {
    $this->negate = true;
    return $this;
  }

  public function containsKey(Tkey $expected): void {
    if ($this->context->containsKey($expected)) {
      if ($this->negate) {
        $this->emitFailure(
          Failure::fromCallStack(
            'Expected Keyed Container to not have key '.
            var_export($expected, true),
          ),
        );
        return;
      }
      $this->emitSuccess();
      return;
    }

    if ($this->negate) {
      $this->emitSuccess();
      return;
    }

    $this->emitFailure(
      Failure::fromCallStack(
        'Expected Keyed Container to have key '.var_export($expected, true),
      ),
    );
    return;
  }

  public function contains(
    Tkey $key,
    Tval $val,
    ?(function(Tkey, Tval, Tval): bool) $comparitor = null,
  ): void {
    if ($comparitor === null) {
      $comparitor = self::identityComparitor();
    }

    if (!$this->context->containsKey($key)) {
      if ($this->negate) {
        $this->emitSuccess();
        return;
      }
      $this->emitFailure(
        Failure::fromCallStack(
          'Expected Keyed Container to have key '.var_export($key, true),
        ),
      );
      return;
    }

    if ($comparitor($key, $this->context->at($key), $val)) {
      if ($this->negate) {
        $this->emitFailure(
          Failure::fromCallStack(
            'Expected Keyed Container to not contain a matching value at key '.
            var_export($key, true),
          ),
        );
        return;
      }
      $this->emitSuccess();
      return;
    }

    if ($this->negate) {
      $this->emitSuccess();
      return;
    }

    $this->emitFailure(
      Failure::fromCallStack(
        'Expected Keyed Container to contain a matching value at key '.
        var_export($key, true),
      ),
    );
  }

  public function containsAll(
    KeyedContainer<Tkey, Tval> $expected,
    ?(function(Tkey, Tval, Tval): bool) $comparitor = null,
  ): void {
    if ($comparitor === null) {
      $comparitor = self::identityComparitor();
    }
    $filtered =
      (new Map($expected))->filterWithKey(
        ($k, $v) ==> {
          if ($this->context->containsKey($k)) {
            return !$comparitor($k, $this->context->at($k), $v);
          }
          return true;
        },
      );

    if ($filtered->count() === 0) {
      if ($this->negate) {
        $this->emitFailure(
          Failure::fromCallStack(
            'Expected Keyed Container to not contain all elements of given list.',
          ),
        );
        return;
      }
      $this->emitSuccess();
      return;
    }

    if ($this->negate) {
      $this->emitSuccess();
      return;
    }
    $this->emitFailure(
      Failure::fromCallStack(
        'Expected Keyed Container to contain all elements of given list.',
      ),
    );
  }

  <<__Memoize>>
  private static function identityComparitor(
  ): (function(Tkey, Tval, Tval): bool) {
    return ($key, $a, $b) ==> $a === $b;
  }
}
