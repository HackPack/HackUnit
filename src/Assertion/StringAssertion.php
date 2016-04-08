<?hh // strict

namespace HackPack\HackUnit\Assertion;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\FailureEmitter;
use HackPack\HackUnit\Event\FailureListener;
use HackPack\HackUnit\Event\SuccessEmitter;
use HackPack\HackUnit\Event\SuccessListener;
use HackPack\HackUnit\Util\Trace;

class StringAssertion
  implements \HackPack\HackUnit\Contract\Assertion\StringAssertion {
  use FailureEmitter;
  use SuccessEmitter;

  private bool $negate = false;

  public function __construct(
    private string $context,
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

  public function is(string $expected): this {
    if ($this->context === $expected) {
      $this->negate
        ? $this->fail(Vector {'Strings expected to be non-identical.'})
        : $this->emitSuccess();
      return $this;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail(
        Vector {
          'Strings expected to be identical:',
          'Context ('.strlen($this->context).'):',
          substr($this->context, 0, 250),
          '',
          'Expected ('.strlen($expected).')',
          substr($expected, 0, 250),
        },
      );
    return $this;
  }

  public function hasLength(int $length): this {
    if (strlen($this->context) === $length) {
      $this->negate
        ? $this->fail(
          Vector {
            'Expected length: '.$length,
            'Context ('.strlen($this->context).'):',
            substr($this->context, 0, 250),
          },
        )
        : $this->emitSuccess();
      return $this;
    }
    $this->negate
      ? $this->emitSuccess()
      : $this->fail(
        Vector {'Expected string to have length different than '.$length},
      );
    return $this;
  }

  public function matches(string $pattern): this {
    $result = preg_match($pattern, $this->context);
    /* HH_FIXME[4118] */
    if ($result === false) {
      $this->fail(
        Vector {
          'Error when matching pattern '.$pattern,
          $this->lastPregError(),
        },
      );
      return $this;
    }

    if ($result === 1) {
      $this->negate
        ? $this->fail(
          Vector {'String expected to not match '.$pattern, $this->context},
        )
        : $this->emitSuccess();
      return $this;
    }

    $this->negate
      ? $this->emitSuccess()
      : $this->fail(
        Vector {'String expected to match '.$pattern, $this->context},
      );
    return $this;
  }

  private function lastPregError(): string {
    switch (preg_last_error()) {
      case PREG_NO_ERROR:
        return 'No error';
      case PREG_INTERNAL_ERROR:
        return 'Internal preg error';
      case PREG_BACKTRACK_LIMIT_ERROR:
        return 'Backtrack limit';
      case PREG_RECURSION_LIMIT_ERROR:
        return 'Recursion limit';
      case PREG_BAD_UTF8_ERROR:
        return 'Invalid UTF8';
      case PREG_BAD_UTF8_OFFSET_ERROR:
        return 'Matched to middle of UTF8 character.';
    }
    return 'Unknown error';
  }

  public function contains(string $needle): this {
    if (strpos($this->context, $needle) === false) {
      $this->negate
        ? $this->emitSuccess()
        : $this->fail(
          Vector {
            'Expected context to contain substring.',
            'Context:',
            $this->context,
            'Substring:',
            $needle,
          },
        );
      return $this;
    }
    $this->negate
      ? $this->fail(
        Vector {
          'Expected context to not contain substring.',
          'Context:',
          $this->context,
          'Substring:',
          $needle,
        },
      )
      : $this->emitSuccess();
    return $this;
  }

  public function containedBy(string $haystack): this {
    if (strpos($haystack, $this->context) === false) {
      $this->negate
        ? $this->emitSuccess()
        : $this->fail(
          Vector {
            'Context:',
            $this->context,
            'Expected to be contained by:',
            $haystack,
          },
        );
      return $this;
    }
    $this->negate
      ? $this->fail(
        Vector {
          'Context:',
          $this->context,
          'Expected to not be contained by:',
          $haystack,
        },
      )
      : $this->emitSuccess();
    return $this;
  }

  private function fail(Vector<string> $lines): void {
    $this->emitFailure(Failure::fromCallStack(implode(PHP_EOL, $lines)));
  }
}
