<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureEmitter;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SuccessEmitter;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class ContainerAssertion<Tval>
  implements
    \HackPack\HackUnit\Contract\Assertion\ContainerAssertion<Tval> {

  use FailureEmitter;
  use SuccessEmitter;

  private bool $negate = false;
  private \ConstVector<Tval> $context;

  public function __construct(
    Container<Tval> $context,
    Vector<FailureListener> $failureListeners,
    Vector<SuccessListener> $successListeners,
  ) {
    $this->context = new Vector($context);
    $this->setFailureListeners($failureListeners);
    $this->setSuccessListeners($successListeners);
  }

  public function not(): this {
    $this->negate = true;
    return $this;
  }

  public function contains(
    Tval $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void {

    if ($this->context->isEmpty()) {
      if ($this->negate) {
        $this->emitSuccess();
        return;
      }
      $this->emitFailure(Failure::fromCallStack('The Container is empty.'));
      return;
    }

    if ($comparitor === null) {
      $comparitor = self::identityComparitor();
    }

    foreach ($this->context as $value) {
      if ($comparitor($expected, $value)) {

        if ($this->negate) {
          $this->emitFailure(
            Failure::fromCallStack(
              'Expected Container to not contain '.
              var_export($expected, true),
            ),
          );
          return;
        }

        $this->emitSuccess();
        return;
      }
    }

    if ($this->negate) {
      $this->emitSuccess();
      return;
    }

    $this->emitFailure(
      Failure::fromCallStack(
        'Expected Container to contain '.var_export($expected, true),
      ),
    );
  }

  public function containsAll(
    Container<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void {
    if ($this->context->isEmpty()) {
      if ($this->negate) {
        $this->emitSuccess();
        return;
      }
      $this->emitFailure(Failure::fromCallStack('The Container is empty.'));
      return;
    }

    if ($comparitor === null) {
      $comparitor = self::identityComparitor();
    }

    foreach ($expected as $other) {

      $otherIsContained = array_reduce(
        $this->context->toArray(),
        ($result, $contextVal) ==> $result ||
        $comparitor($contextVal, $other),
        false,
      );

      if ($otherIsContained) {
        continue;
      }

      if ($this->negate) {
        $this->emitSuccess();
        return;
      }
      $this->emitFailure(
        Failure::fromCallStack(
          'Container expected to contain '.var_export($other, true),
        ),
      );
      return;
    }

    if ($this->negate) {
      $this->emitFailure(
        Failure::fromCallStack(
          'Container expected to not contain all of the given list.',
        ),
      );
      return;
    }
    $this->emitSuccess();

  }

  public function containsAny(
    Container<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void {
    if ($this->context->isEmpty()) {
      if ($this->negate) {
        $this->emitSuccess();
        return;
      }
      $this->emitFailure(Failure::fromCallStack('The Container is empty.'));
      return;
    }

    if ($comparitor === null) {
      $comparitor = self::identityComparitor();
    }

    foreach ($expected as $otherValue) {

      $otherIsContained = array_reduce(
        $this->context->toArray(),
        ($result, $contextVal) ==> $result ||
        $comparitor($contextVal, $otherValue),
        false,
      );

      if ($otherIsContained) {
        if ($this->negate) {
          $this->emitFailure(
            Failure::fromCallStack(
              'Container expected to not contain '.
              var_export($otherValue, true),
            ),
          );
          return;
        }
        $this->emitSuccess();
        return;
      }
    }

    if ($this->negate) {
      $this->emitSuccess();
      return;
    }

    $this->emitFailure(
      Failure::fromCallStack(
        'Container expected to contain at least one item from the list.',
      ),
    );
  }

  public function containsOnly(
    Container<Tval> $expected,
    ?(function(Tval, Tval): bool) $comparitor = null,
  ): void {
    $expected = new Vector($expected);

    if ($this->context->count() !== $expected->count()) {
      if ($this->negate) {
        $this->emitSuccess();
        return;
      }
      $message =
        $this->context->count() > $expected->count()
          ? 'Container contains more elements than expected.'
          : 'Container contains fewer elements than expected.';
      $this->emitFailure(Failure::fromCallStack($message));
      return;
    }

    if ($this->context->isEmpty()) {
      if ($this->negate) {
        $this->emitFailure(
          Failure::fromCallStack('Traverseable expected to not be empty.'),
        );
        return;
      }
      $this->emitSuccess();
      return;
    }

    $this->containsAll($expected, $comparitor);

  }

  public function isEmpty(): void {
    if ($this->context->isEmpty()) {
      if ($this->negate) {
        $this->emitFailure(
          Failure::fromCallStack('Container expected not to be empty.'),
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
      Failure::fromCallStack('Container expected to be empty.'),
    );
  }

  <<__Memoize>>
  private static function identityComparitor<T>(): (function(T, T): bool) {
    return ($a, $b) ==> $a === $b;
  }
}
